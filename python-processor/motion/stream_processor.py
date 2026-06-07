import datetime as dt
from dataclasses import dataclass
from pathlib import Path

import cv2

from motion.clip_writer import ClipWriter
from motion.detector import MotionDetector


@dataclass(frozen=True)
class SavedClip:
    output_path: Path
    started_at: dt.datetime
    ended_at: dt.datetime
    duration_seconds: int


class StreamProcessor:
    """Process one video stream and save clips when motion starts and stops."""

    def __init__(
        self,
        stream_url: str,
        output_directory: str | Path,
        detector: MotionDetector | None = None,
        clip_writer: ClipWriter | None = None,
        codec: str = "VP80",
        output_extension: str = "webm",
        fps: int = 20,
        quiet_frames_to_stop: int = 32,
        capture_factory=cv2.VideoCapture,
    ) -> None:
        self.stream_url = stream_url
        self.output_directory = Path(output_directory)
        self.detector = detector or MotionDetector()
        self.clip_writer = clip_writer or ClipWriter(buffer_size=quiet_frames_to_stop)
        self.codec = codec
        self.output_extension = output_extension.lstrip(".")
        self.fps = fps
        self.quiet_frames_to_stop = quiet_frames_to_stop
        self.capture_factory = capture_factory

        self.quiet_frame_count = 0
        self.recording_started_at: dt.datetime | None = None
        self.current_output_path: Path | None = None

    def process_forever(self, stop_event=None):
        capture = self.capture_factory(self.stream_url)

        try:
            while True:
                if stop_event is not None and stop_event.is_set():
                    break

                success, frame = capture.read()

                if not success or frame is None:
                    break

                saved_clip = self.process_frame(frame)

                if saved_clip is not None:
                    yield saved_clip
        finally:
            capture.release()

            if self.clip_writer.recording:
                yield self.finish_recording()

    def process_frame(self, frame) -> SavedClip | None:
        result = self.detector.process(frame)
        self.clip_writer.update(result.processed_frame)

        if result.motion_detected:
            self.quiet_frame_count = 0

            if not self.clip_writer.recording:
                self.start_recording()

            return None

        if self.clip_writer.recording:
            self.quiet_frame_count += 1

            if self.quiet_frame_count >= self.quiet_frames_to_stop:
                return self.finish_recording()

        return None

    def start_recording(self) -> Path:
        self.output_directory.mkdir(parents=True, exist_ok=True)
        self.recording_started_at = dt.datetime.now()
        output_path = self.output_directory / f"{self.recording_started_at:%Y%m%d-%H%M%S}.{self.output_extension}"
        fourcc = cv2.VideoWriter_fourcc(*self.codec)

        self.current_output_path = output_path
        self.clip_writer.start(str(output_path), fourcc, self.fps)

        return output_path

    def finish_recording(self) -> SavedClip:
        ended_at = dt.datetime.now()
        started_at = self.recording_started_at or ended_at
        duration_seconds = max(0, int((ended_at - started_at).total_seconds()))
        output_path = self.current_output_path or Path("")

        self.clip_writer.finish()
        self.quiet_frame_count = 0
        self.recording_started_at = None
        self.current_output_path = None

        return SavedClip(
            output_path=output_path,
            started_at=started_at,
            ended_at=ended_at,
            duration_seconds=duration_seconds,
        )
