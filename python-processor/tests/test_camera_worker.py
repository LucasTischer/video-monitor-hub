import datetime as dt
import sys
from pathlib import Path
from types import SimpleNamespace

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from services.laravel_api import ProcessorCamera
from workers.camera_worker import CameraWorker


class FakeProcessor:
    created = []

    def __init__(self, stream_url, output_directory, **kwargs):
        self.stream_url = stream_url
        self.output_directory = output_directory
        self.kwargs = kwargs
        FakeProcessor.created.append(self)

    def process_forever(self, stop_event=None):
        yield SimpleNamespace(
            output_path=Path("/app/storage/videos/clip.webm"),
            started_at=dt.datetime(2026, 6, 7, 10, 0, 0),
            ended_at=dt.datetime(2026, 6, 7, 10, 0, 5),
            duration_seconds=5,
        )


class FakeApiClient:
    def __init__(self):
        self.registered = []

    def register_video(self, camera_id, saved_clip):
        self.registered.append((camera_id, saved_clip.output_path))


def setup_function():
    FakeProcessor.created = []


def test_camera_worker_registers_saved_clips():
    camera = ProcessorCamera(
        id=7,
        name="Front Gate",
        stream_url="http://camera.local/front",
    )
    api_client = FakeApiClient()
    worker = CameraWorker(
        camera=camera,
        output_directory="/app/storage/videos",
        api_client=api_client,
        processor_factory=FakeProcessor,
    )

    worker.run()

    assert worker.last_error is None
    assert api_client.registered == [(7, Path("/app/storage/videos/clip.webm"))]


def test_camera_worker_passes_recording_settings_to_processor():
    camera = ProcessorCamera(
        id=9,
        name="Side Door",
        stream_url="http://camera.local/side",
        record_after_motion_seconds=5,
        pre_motion_buffer_seconds=2,
        recording_resolution_height=720,
        recording_fps=10,
        timezone="America/Sao_Paulo",
    )
    worker = CameraWorker(
        camera=camera,
        output_directory="/app/storage/videos",
        processor_factory=FakeProcessor,
    )

    worker.run()

    processor = FakeProcessor.created[0]

    assert worker.last_error is None
    assert processor.kwargs["fps"] == 10
    assert processor.kwargs["quiet_frames_to_stop"] == 50
    assert processor.kwargs["pre_motion_buffer_frames"] == 20
    assert processor.kwargs["recording_resolution_height"] == 720
    assert processor.kwargs["timezone"] == "America/Sao_Paulo"


def test_camera_worker_captures_processor_errors():
    class FailingProcessor(FakeProcessor):
        def process_forever(self, stop_event=None):
            raise RuntimeError("stream failed")
            yield

    camera = ProcessorCamera(
        id=8,
        name="Garage",
        stream_url="http://camera.local/garage",
    )
    worker = CameraWorker(
        camera=camera,
        output_directory="/app/storage/videos",
        processor_factory=FailingProcessor,
    )

    worker.run()

    assert isinstance(worker.last_error, RuntimeError)
