import sys
from pathlib import Path
from types import SimpleNamespace

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from services.laravel_api import LaravelApiClient


class FakeResponse:
    def __init__(self, payload):
        self.payload = payload

    def json(self):
        return self.payload

    def raise_for_status(self):
        return None


def test_active_cameras_maps_laravel_response(monkeypatch):
    def fake_get(url, headers, timeout):
        assert url == "http://laravel-app/api/processor/cameras"
        assert headers["Authorization"] == "Bearer token"
        assert timeout == 10

        return FakeResponse({
            "data": [
                {
                    "id": 1,
                    "name": "Front Gate",
                    "stream_url": "http://camera.local/front",
                    "location": "Entrance",
                    "record_after_motion_seconds": 5,
                    "pre_motion_buffer_seconds": 2,
                }
            ]
        })

    monkeypatch.setattr("services.laravel_api.requests.get", fake_get)

    cameras = LaravelApiClient("http://laravel-app/api", "token").active_cameras()

    assert len(cameras) == 1
    assert cameras[0].id == 1
    assert cameras[0].name == "Front Gate"
    assert cameras[0].stream_url == "http://camera.local/front"
    assert cameras[0].record_after_motion_seconds == 5
    assert cameras[0].pre_motion_buffer_seconds == 2


def test_register_video_posts_saved_clip_payload(monkeypatch):
    posted_payload = {}

    def fake_post(url, json, headers, timeout):
        assert url == "http://laravel-app/api/processor/videos"
        assert headers["Authorization"] == "Bearer token"
        posted_payload.update(json)

        return FakeResponse({
            "data": {
                "id": 10,
                "camera_id": 1,
                "filename": "clip.avi",
                "path": "/storage/videos/clip.avi",
            }
        })

    monkeypatch.setattr("services.laravel_api.requests.post", fake_post)

    saved_clip = SimpleNamespace(
        output_path=Path("/app/storage/videos/clip.avi"),
        started_at=SimpleNamespace(isoformat=lambda sep, timespec: "2026-06-07 10:00:00"),
        ended_at=SimpleNamespace(isoformat=lambda sep, timespec: "2026-06-07 10:00:12"),
        duration_seconds=12,
    )

    response = LaravelApiClient("http://laravel-app/api", "token").register_video(1, saved_clip)

    assert response["id"] == 10
    assert posted_payload["camera_id"] == 1
    assert posted_payload["filename"] == "clip.avi"
    assert posted_payload["path"] == "/storage/videos/clip.avi"
    assert posted_payload["duration_seconds"] == 12
