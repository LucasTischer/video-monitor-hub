import os
import time

from services.laravel_api import LaravelApiClient, ProcessorCamera
from workers.supervisor import ProcessorSupervisor


def main() -> None:
    api_base_url = os.getenv("PROCESSOR_API_BASE_URL")
    api_token = os.getenv("PROCESSOR_API_TOKEN")
    stream_url = os.getenv("PROCESSOR_CAMERA_URL")
    output_directory = os.getenv("PROCESSOR_OUTPUT_DIR", "/app/storage/videos")
    refresh_seconds = int(os.getenv("PROCESSOR_REFRESH_SECONDS", "10"))

    print("Python Video Processor started.", flush=True)

    api_client = LaravelApiClient(api_base_url, api_token) if api_base_url and api_token else None
    supervisor = ProcessorSupervisor(
        output_directory=output_directory,
        api_client=api_client,
    )

    try:
        while True:
            cameras = cameras_to_process(api_base_url, api_token, stream_url)

            if not cameras:
                print(f"No cameras configured. Waiting {refresh_seconds} seconds.", flush=True)
                supervisor.sync_cameras([])
                time.sleep(refresh_seconds)
                continue

            supervisor.sync_cameras(cameras)
            print(f"Active camera workers: {supervisor.active_worker_count()}", flush=True)

            time.sleep(refresh_seconds)
    except KeyboardInterrupt:
        print("Stopping Python Video Processor.", flush=True)
        supervisor.stop_all()


def cameras_to_process(api_base_url: str | None, api_token: str | None, fallback_stream_url: str | None) -> list[ProcessorCamera]:
    if api_base_url and api_token:
        try:
            return LaravelApiClient(api_base_url, api_token).active_cameras()
        except Exception as exception:
            print(f"Could not fetch cameras from Laravel: {exception}", flush=True)

    if fallback_stream_url:
        return [
            ProcessorCamera(
                id=0,
                name="Manual camera",
                stream_url=fallback_stream_url,
            )
        ]

    return []

if __name__ == "__main__":
    main()
