from pathlib import Path
from threading import Event, Thread
from typing import Callable

from motion.stream_processor import StreamProcessor
from services.laravel_api import LaravelApiClient, ProcessorCamera


class CameraWorker(Thread):
    """Run one camera stream processor in its own thread."""

    def __init__(
        self,
        camera: ProcessorCamera,
        output_directory: str | Path,
        api_client: LaravelApiClient | None = None,
        processor_factory: Callable = StreamProcessor,
    ) -> None:
        super().__init__(daemon=True, name=f"camera-worker-{camera.id}")
        self.camera = camera
        self.output_directory = output_directory
        self.api_client = api_client
        self.processor_factory = processor_factory
        self.stop_event = Event()
        self.last_error: Exception | None = None

    @property
    def camera_id(self) -> int:
        return self.camera.id

    def stop(self) -> None:
        self.stop_event.set()

    def run(self) -> None:
        try:
            self._process_camera()
        except Exception as exception:
            self.last_error = exception
            print(f"Camera worker failed for {self.camera.id}: {exception}", flush=True)

    def _process_camera(self) -> None:
        processor = self.processor_factory(
            stream_url=self.camera.stream_url,
            output_directory=self.output_directory,
            fps=self.camera.recording_fps,
            quiet_frames_to_stop=max(1, self.camera.record_after_motion_seconds * self.camera.recording_fps),
            pre_motion_buffer_frames=max(1, self.camera.pre_motion_buffer_seconds * self.camera.recording_fps),
            recording_resolution_height=self.camera.recording_resolution_height,
            timezone=self.camera.timezone,
        )

        print(f"Processing camera {self.camera.id}: {self.camera.name}", flush=True)

        for saved_clip in processor.process_forever(stop_event=self.stop_event):
            print(f"Saved motion clip for camera {self.camera.id}: {saved_clip.output_path}", flush=True)

            if self.api_client and self.camera.id:
                try:
                    self.api_client.register_video(self.camera.id, saved_clip)
                except Exception as exception:
                    print(f"Could not register video in Laravel for camera {self.camera.id}: {exception}", flush=True)
