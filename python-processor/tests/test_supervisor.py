import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from services.laravel_api import ProcessorCamera
from workers.supervisor import ProcessorSupervisor


class FakeWorker:
    created = []

    def __init__(self, camera, output_directory, api_client=None):
        self.camera = camera
        self.output_directory = output_directory
        self.api_client = api_client
        self.started = False
        self.stopped = False
        self.alive = False
        FakeWorker.created.append(self)

    def start(self):
        self.started = True
        self.alive = True

    def stop(self):
        self.stopped = True
        self.alive = False

    def is_alive(self):
        return self.alive

    @property
    def camera_id(self):
        return self.camera.id


def camera(camera_id, name="Camera"):
    return ProcessorCamera(
        id=camera_id,
        name=f"{name} {camera_id}",
        stream_url=f"http://camera.local/{camera_id}",
    )


def setup_function():
    FakeWorker.created = []


def test_supervisor_starts_one_worker_per_camera():
    supervisor = ProcessorSupervisor(
        output_directory="/app/storage/videos",
        worker_factory=FakeWorker,
    )

    supervisor.sync_cameras([camera(1), camera(2)])

    assert supervisor.active_worker_count() == 2
    assert [worker.camera_id for worker in FakeWorker.created] == [1, 2]
    assert all(worker.started for worker in FakeWorker.created)


def test_supervisor_does_not_duplicate_existing_workers():
    supervisor = ProcessorSupervisor(
        output_directory="/app/storage/videos",
        worker_factory=FakeWorker,
    )

    supervisor.sync_cameras([camera(1)])
    supervisor.sync_cameras([camera(1)])

    assert supervisor.active_worker_count() == 1
    assert len(FakeWorker.created) == 1


def test_supervisor_stops_workers_for_removed_cameras():
    supervisor = ProcessorSupervisor(
        output_directory="/app/storage/videos",
        worker_factory=FakeWorker,
    )

    supervisor.sync_cameras([camera(1), camera(2)])
    removed_worker = supervisor.workers[2]

    supervisor.sync_cameras([camera(1)])

    assert supervisor.active_worker_count() == 1
    assert removed_worker.stopped is True
    assert 2 not in supervisor.workers


def test_supervisor_restarts_workers_when_camera_settings_change():
    supervisor = ProcessorSupervisor(
        output_directory="/app/storage/videos",
        worker_factory=FakeWorker,
    )

    supervisor.sync_cameras([camera(1)])
    original_worker = FakeWorker.created[0]

    changed_camera = ProcessorCamera(
        id=1,
        name="Camera 1",
        stream_url="http://camera.local/1",
        timezone="America/Sao_Paulo",
    )

    supervisor.sync_cameras([changed_camera])

    assert original_worker.stopped is True
    assert len(FakeWorker.created) == 2
    assert FakeWorker.created[1].camera == changed_camera


def test_supervisor_removes_finished_workers():
    supervisor = ProcessorSupervisor(
        output_directory="/app/storage/videos",
        worker_factory=FakeWorker,
    )

    supervisor.sync_cameras([camera(1)])
    supervisor.workers[1].alive = False

    assert supervisor.active_worker_count() == 0
