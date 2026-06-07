import os
import time

from motion.stream_processor import StreamProcessor
from services.laravel_api import LaravelApiClient, ProcessorCamera


def main() -> None:
    api_base_url = os.getenv("PROCESSOR_API_BASE_URL")
    api_token = os.getenv("PROCESSOR_API_TOKEN")
    stream_url = os.getenv("PROCESSOR_CAMERA_URL")
    output_directory = os.getenv("PROCESSOR_OUTPUT_DIR", "/app/storage/videos")

    print("Python Video Processor started.", flush=True)

    while True:
        cameras = cameras_to_process(api_base_url, api_token, stream_url)

        if not cameras:
            print("No cameras configured. Waiting 10 seconds.", flush=True)
            time.sleep(10)
            continue

        api_client = LaravelApiClient(api_base_url, api_token) if api_base_url and api_token else None

        for camera in cameras:
            process_camera(camera, output_directory, api_client)

        time.sleep(10)


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


def process_camera(camera: ProcessorCamera, output_directory: str, api_client: LaravelApiClient | None) -> None:
    processor = StreamProcessor(
        stream_url=camera.stream_url,
        output_directory=output_directory,
    )

    print(f"Processing camera {camera.id}: {camera.name}", flush=True)

    for saved_clip in processor.process_forever():
        print(f"Saved motion clip: {saved_clip.output_path}", flush=True)

        if api_client and camera.id:
            try:
                api_client.register_video(camera.id, saved_clip)
            except Exception as exception:
                print(f"Could not register video in Laravel: {exception}", flush=True)


if __name__ == "__main__":
    main()
