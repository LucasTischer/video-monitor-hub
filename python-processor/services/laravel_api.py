from dataclasses import dataclass
from pathlib import Path

import requests


@dataclass(frozen=True)
class ProcessorCamera:
    id: int
    name: str
    stream_url: str
    location: str | None = None


class LaravelApiClient:
    def __init__(self, base_url: str, api_token: str, timeout: int = 10) -> None:
        self.base_url = base_url.rstrip("/")
        self.api_token = api_token
        self.timeout = timeout

    def active_cameras(self) -> list[ProcessorCamera]:
        response = requests.get(
            f"{self.base_url}/processor/cameras",
            headers=self._headers(),
            timeout=self.timeout,
        )
        response.raise_for_status()

        return [
            ProcessorCamera(
                id=camera["id"],
                name=camera["name"],
                stream_url=camera["stream_url"],
                location=camera.get("location"),
            )
            for camera in response.json()["data"]
        ]

    def register_video(self, camera_id: int, saved_clip) -> dict:
        response = requests.post(
            f"{self.base_url}/processor/videos",
            json={
                "camera_id": camera_id,
                "filename": Path(saved_clip.output_path).name,
                "path": f"/storage/videos/{Path(saved_clip.output_path).name}",
                "started_at": saved_clip.started_at.isoformat(sep=" ", timespec="seconds"),
                "ended_at": saved_clip.ended_at.isoformat(sep=" ", timespec="seconds"),
                "duration_seconds": saved_clip.duration_seconds,
                "motion_detected": True,
                "metadata": {
                    "source": "python-processor",
                },
            },
            headers=self._headers(),
            timeout=self.timeout,
        )
        response.raise_for_status()

        return response.json()["data"]

    def _headers(self) -> dict[str, str]:
        return {
            "Authorization": f"Bearer {self.api_token}",
            "Accept": "application/json",
        }
