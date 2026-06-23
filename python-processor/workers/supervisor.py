from pathlib import Path
from typing import Callable

from services.laravel_api import LaravelApiClient, ProcessorCamera
from workers.camera_worker import CameraWorker


class ProcessorSupervisor:
    """Keep one worker running for each active camera."""

    def __init__(
        self,
        output_directory: str | Path,
        api_client: LaravelApiClient | None = None,
        worker_factory: Callable = CameraWorker,
    ) -> None:
        self.output_directory = output_directory
        self.api_client = api_client
        self.worker_factory = worker_factory
        self.workers: dict[int, CameraWorker] = {}

    def sync_cameras(self, cameras: list[ProcessorCamera]) -> None:
        active_camera_ids = {camera.id for camera in cameras}

        self._remove_finished_workers()
        self._stop_changed_workers(cameras)
        self._stop_missing_workers(active_camera_ids)

        for camera in cameras:
            if camera.id in self.workers:
                continue

            worker = self.worker_factory(
                camera=camera,
                output_directory=self.output_directory,
                api_client=self.api_client,
            )
            worker.start()
            self.workers[camera.id] = worker

    def stop_all(self) -> None:
        for worker in self.workers.values():
            worker.stop()

    def active_worker_count(self) -> int:
        self._remove_finished_workers()

        return len(self.workers)

    def _remove_finished_workers(self) -> None:
        for camera_id, worker in list(self.workers.items()):
            if not worker.is_alive():
                del self.workers[camera_id]

    def _stop_missing_workers(self, active_camera_ids: set[int]) -> None:
        for camera_id, worker in list(self.workers.items()):
            if camera_id not in active_camera_ids:
                worker.stop()
                del self.workers[camera_id]

    def _stop_changed_workers(self, cameras: list[ProcessorCamera]) -> None:
        cameras_by_id = {camera.id: camera for camera in cameras}

        for camera_id, worker in list(self.workers.items()):
            if camera_id in cameras_by_id and worker.camera != cameras_by_id[camera_id]:
                worker.stop()
                del self.workers[camera_id]
