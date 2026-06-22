# Video Monitor Hub

Video Monitor Hub is a Laravel and Python/OpenCV application for managing camera streams and reviewing recordings created when motion is detected.

This project is a portfolio rebuild of an older PHP/Python video monitoring application. The goal is not only to recreate the original features, but also to show a cleaner development lifecycle: planning, Docker setup, framework adoption, database modeling, tests, API integration, and iterative improvements.

## Features

- User registration, login, profile management, and logout through Laravel Breeze.
- Authenticated camera CRUD with ownership and shared-access policies.
- Camera sharing with viewer, editor, and manager roles.
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

Camera authorization is centered on the camera record. Each camera has one owner through `cameras.user_id` and can also be shared with other users through `camera_shares`. Video access inherits the camera permissions, so users can only list, view, or delete recordings when they have the required access to the related camera.

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
laravel-scheduler Laravel scheduler worker for recurring Artisan tasks
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
./bin/artisan migrate
```

Seed demo data:

```bash
./bin/artisan db:seed
```

Use `./bin/artisan` for Laravel Artisan commands during development. It runs
Artisan inside the `laravel-app` container as your host user, so generated files
remain editable from VS Code:

```bash
./bin/artisan make:policy CameraPolicy --model=Camera
```

Open the app:

```text
http://localhost:8000
```

Demo login:

```text
Email: demo@example.com
Password: password
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
./bin/artisan test
```

Run Python processor tests:

```bash
docker compose run --rm python-processor pytest
```

## Scheduled Tasks

The `laravel-scheduler` service runs Laravel's scheduler with:

```bash
php artisan schedule:work
```

It executes scheduled Laravel commands, including:

```text
videos:prune-expired
```

That command runs daily and deletes expired recordings according to each camera's recording retention setting.

Inspect scheduled tasks:

```bash
./bin/artisan schedule:list
```

Start or restart the scheduler service:

```bash
docker compose up -d laravel-scheduler
```

## Demo Data

The database seeder creates:

- one demo user;
- three inactive demo cameras;
- three playable WebM demo recordings.

Demo cameras are inactive by default so the Python processor does not try to connect to fake stream URLs. Activate a camera and replace its stream URL when you want the processor to handle a real source.

## Camera Sharing

Camera owners can share a camera from the camera detail page by entering another user's email and assigning a role:

```text
viewer   View camera details and recordings
editor   View camera details/recordings and edit camera settings
manager  View, edit, delete, and manage shared access
```

Shared cameras appear in the shared user's camera list, dashboard, and video list. Users without ownership or a share record cannot access the camera or its recordings.

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
3. Python starts one worker thread per active camera.
4. Each worker opens its camera stream.
5. OpenCV detects meaningful frame changes.
6. Python saves a WebM recording when motion stops.
7. Python calls `POST /api/processor/videos`.
8. Laravel stores the recording metadata in PostgreSQL.
9. The user reviews the recording in Laravel.

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

The processor refreshes Laravel camera configuration every 10 seconds by default. Override this with:

```bash
PROCESSOR_REFRESH_SECONDS=5 docker compose up -d --build python-processor
```

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
./bin/artisan migrate
```

Open a Laravel shell:

```bash
./bin/artisan tinker
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
./bin/artisan migrate
```

## Current Status

Implemented:

- Dockerized Laravel, Python, and PostgreSQL services.
- Laravel Breeze authentication.
- Camera CRUD.
- Camera sharing with viewer/editor/manager roles.
- Policy-based camera and video authorization.
- Video management pages.
- Dashboard metrics.
- Python motion detector and clip writer.
- Python-to-Laravel API integration.
- Shared video storage.
- Browser-compatible WebM recording output.
- Scheduled expired recording cleanup.
- Separate Laravel testing database.
- Laravel and Python automated tests.
- Multi-camera processor workers.

Planned next improvements:

- Camera processing status in Laravel.
- Sharing UI polish and role indicators in camera/video lists.
- Screenshots/GIFs for the README.
- GitHub Actions for Laravel and Python tests.

## Documentation

Full functional and technical notes are available in [docs/project-documentation.md](docs/project-documentation.md).
