# Video Monitor Hub

Video Monitor Hub is a Laravel and Python/OpenCV application for managing camera streams and reviewing recordings created when motion is detected.

This project is a portfolio rebuild of an older PHP/Python video monitoring application. The goal is not only to recreate the original features, but also to show a cleaner development lifecycle: planning, Docker setup, framework adoption, database modeling, tests, API integration, and iterative improvements.

## Features

- User registration, login, profile management, and logout through Laravel Breeze.
- Authenticated camera CRUD with user ownership checks.
- Dashboard with camera and recording metrics.
- Video recording list and detail pages.
- Browser playback for new WebM recordings.
- Python/OpenCV motion detection pipeline.
- Python processor integration with Laravel through internal API endpoints.
- Shared Docker storage for generated recordings.
- PostgreSQL development and testing databases.
- Laravel feature tests and Python processor tests.

## Tech Stack

```text
Laravel 12        Web app, auth, CRUD, API, database-backed UI
PHP 8.4 + Apache  Laravel runtime container
PostgreSQL 16     Application and test databases
Python 3.11       Motion processing service
OpenCV            Frame processing and recording
Docker Compose    Local development environment
Breeze + Vite     Auth scaffolding and frontend assets
Pest/PHPUnit      Laravel tests
pytest            Python tests
Xdebug            Local PHP step debugging
```

## Architecture

```text
Browser
  |
  v
Laravel Web App  <-----------------------------+
  |                                            |
  | reads/writes                               | POST /api/processor/videos
  v                                            |
PostgreSQL                                    |
  ^                                            |
  | GET /api/processor/cameras                 |
  |                                            |
Python Processor ---- reads camera stream ----+
  |
  | writes .webm recordings
  v
Shared Docker Volume
  |
  v
Laravel public storage: /storage/videos/{filename}
```

Laravel is the control center. It owns users, cameras, videos, validation, authorization, and the browser UI.

The Python processor is the worker. It asks Laravel for active cameras, processes each stream with OpenCV, saves recordings, and reports completed clips back to Laravel.

Docker connects the services on an internal network. The Python container talks to Laravel at:

```text
http://laravel-app/api
```

## Project Structure

```text
.
+-- docker-compose.yml
+-- docker/
|   +-- apache/          # Apache virtual host for Laravel
|   +-- database/        # PostgreSQL initialization scripts
|   +-- laravel/         # Laravel Docker image, entrypoint, Xdebug config
+-- docs/                # Project documentation
+-- laravel-app/         # Laravel application
+-- python-processor/    # Python/OpenCV motion detection service
```

## Docker Services

```text
laravel-app       Laravel application served by Apache/PHP
python-processor  Python service for video processing
database          PostgreSQL database
```

The Laravel container prepares the app on startup by:

- creating `laravel-app/.env` from `.env.example` when needed;
- applying Docker database defaults to a new `.env`;
- installing Composer dependencies into a Docker volume;
- generating `APP_KEY` when needed;
- creating Laravel's `public/storage` symlink;
- ensuring the test database exists;
- installing Node dependencies into a Docker volume;
- building Vite assets when needed.

## Local Setup

Start the stack:

```bash
docker compose up -d --build
```

Run migrations:

```bash
docker compose exec laravel-app php artisan migrate
```

Open the app:

```text
http://localhost:8000
```

PostgreSQL is exposed on port `5432`.

Default database values:

```text
DB_DATABASE=video_monitor
DB_TEST_DATABASE=video_monitor_test
DB_USERNAME=postgres
DB_PASSWORD=secret
```

Inside `laravel-app/.env`, the Docker database connection should be:

```text
DB_CONNECTION=pgsql
DB_HOST=database
DB_PORT=5432
DB_DATABASE=video_monitor
DB_TEST_DATABASE=video_monitor_test
DB_USERNAME=postgres
DB_PASSWORD=secret
```

## Testing

Laravel tests use the separate PostgreSQL database:

```text
video_monitor_test
```

This keeps `php artisan test` from clearing local development data in `video_monitor`. Docker creates the test database automatically on fresh database volumes, and the Laravel container also checks for it on startup.

Run Laravel tests:

```bash
docker compose exec laravel-app php artisan test
```

Run Python processor tests:

```bash
docker compose run --rm python-processor pytest
```

## Processor Integration

The Python processor connects to Laravel through internal API endpoints secured by `PROCESSOR_API_TOKEN`.

Laravel exposes:

```text
GET  /api/processor/cameras
POST /api/processor/videos
```

Processing flow:

1. User creates and activates a camera in Laravel.
2. Python calls `GET /api/processor/cameras`.
3. Python opens each active camera stream.
4. OpenCV detects meaningful frame changes.
5. Python saves a WebM recording when motion stops.
6. Python calls `POST /api/processor/videos`.
7. Laravel stores the recording metadata in PostgreSQL.
8. The user reviews the recording in Laravel.

The shared recording volume is mounted at:

```text
python-processor: /app/storage/videos
laravel-app:      /var/www/html/storage/app/public/videos
```

Laravel serves those files through:

```text
/storage/videos/{filename}
```

New recordings are written as WebM files for browser playback.

## Manual Processor Testing

To test one camera stream manually, pass `PROCESSOR_CAMERA_URL` when starting the processor:

```bash
PROCESSOR_CAMERA_URL=http://example-camera.local/video docker compose up -d --build python-processor
```

Watch processor logs:

```bash
docker compose logs -f python-processor
```

The normal Laravel-driven flow uses active cameras from the database, so `PROCESSOR_CAMERA_URL` is mainly useful as a fallback/debug path.

## Debugging With Xdebug

The Laravel Docker image includes Xdebug for local step debugging in VS Code.

1. Install the recommended VS Code extensions from `.vscode/extensions.json`.
2. Open the Run and Debug panel.
3. Select `Listen for Xdebug`.
4. Start debugging.
5. Add a breakpoint in a Laravel controller, route, request, or model.
6. Use the application at `http://localhost:8000`.

Xdebug listens on port `9003` and maps the container path `/var/www/html` to the local `laravel-app` folder.

To disable Xdebug temporarily:

```bash
XDEBUG_MODE=off docker compose up -d --build laravel-app
```

## Useful Commands

Rebuild the stack:

```bash
docker compose up -d --build --force-recreate
```

Run migrations:

```bash
docker compose exec laravel-app php artisan migrate
```

Open a Laravel shell:

```bash
docker compose exec laravel-app php artisan tinker
```

Watch Laravel logs:

```bash
docker compose logs -f laravel-app
```

Watch processor logs:

```bash
docker compose logs -f python-processor
```

Reset development data:

```bash
docker compose down -v
docker compose up -d --build
docker compose exec laravel-app php artisan migrate
```

## Current Status

Implemented:

- Dockerized Laravel, Python, and PostgreSQL services.
- Laravel Breeze authentication.
- Camera CRUD.
- Video management pages.
- Dashboard metrics.
- Python motion detector and clip writer.
- Python-to-Laravel API integration.
- Shared video storage.
- Browser-compatible WebM recording output.
- Separate Laravel testing database.
- Laravel and Python automated tests.

Planned next improvements:

- Multi-camera processor workers.
- Camera processing status in Laravel.
- Better dashboard and table UI.
- Demo seed data.
- Screenshots/GIFs for the README.
- GitHub Actions for Laravel and Python tests.

## Documentation

Full functional and technical notes are available in [docs/project-documentation.md](docs/project-documentation.md).
