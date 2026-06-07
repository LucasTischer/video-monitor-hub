import sys
from pathlib import Path

import numpy as np

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from motion.detector import MotionDetectionResult
from motion.stream_processor import StreamProcessor


class FakeDetector:
    def __init__(self, results):
        self.results = list(results)

    def process(self, frame):
        motion_detected = self.results.pop(0)

        return MotionDetectionResult(
            motion_detected=motion_detected,
            contour_count=1 if motion_detected else 0,
            largest_contour_area=1000 if motion_detected else 0,
            processed_frame=frame,
        )


class FakeClipWriter:
    def __init__(self):
        self.recording = False
        self.started_paths = []
        self.updated_frames = []
        self.finish_count = 0

    def update(self, frame):
        self.updated_frames.append(frame)

    def start(self, output_path, fourcc, fps):
        self.recording = True
        self.started_paths.append(output_path)

    def finish(self):
        self.recording = False
        self.finish_count += 1


def make_frame():
    return np.zeros((20, 30, 3), dtype=np.uint8)


def test_stream_processor_starts_recording_when_motion_is_detected(tmp_path):
    clip_writer = FakeClipWriter()
    processor = StreamProcessor(
        stream_url="http://camera.local/video",
        output_directory=tmp_path,
        detector=FakeDetector([True]),
        clip_writer=clip_writer,
    )

    saved_clip = processor.process_frame(make_frame())

    assert saved_clip is None
    assert clip_writer.recording is True
    assert len(clip_writer.started_paths) == 1
    assert clip_writer.started_paths[0].endswith(".avi")


def test_stream_processor_finishes_after_configured_quiet_frames(tmp_path):
    clip_writer = FakeClipWriter()
    processor = StreamProcessor(
        stream_url="http://camera.local/video",
        output_directory=tmp_path,
        detector=FakeDetector([True, False, False]),
        clip_writer=clip_writer,
        quiet_frames_to_stop=2,
    )

    assert processor.process_frame(make_frame()) is None
    assert processor.process_frame(make_frame()) is None

    saved_clip = processor.process_frame(make_frame())

    assert saved_clip is not None
    assert saved_clip.output_path.suffix == ".avi"
    assert clip_writer.recording is False
    assert clip_writer.finish_count == 1


def test_stream_processor_does_not_record_without_motion(tmp_path):
    clip_writer = FakeClipWriter()
    processor = StreamProcessor(
        stream_url="http://camera.local/video",
        output_directory=tmp_path,
        detector=FakeDetector([False, False]),
        clip_writer=clip_writer,
    )

    assert processor.process_frame(make_frame()) is None
    assert processor.process_frame(make_frame()) is None
    assert clip_writer.recording is False
    assert clip_writer.finish_count == 0
