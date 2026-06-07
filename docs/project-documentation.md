# Project Documentation

## Overview

Video Monitor Hub is designed to help users manage camera streams and review videos recorded when motion is detected. The system has three main runtime parts:

- A Laravel web application for authentication, camera management, recording listing, and video playback.
- A Python processor that uses OpenCV to detect motion, record videos, and register recordings through Laravel's processor API.
- A PostgreSQL database used by Laravel for users, cameras, and recording metadata.

## System Components

### Laravel Application

The Laravel application is responsible for the user interface and database-backed management features. It handles:

- User authentication.
- Camera CRUD operations.
- Listing recent motion recordings.
- Playing recorded videos in the browser.
- Navigation between the dashboard, camera management, video management, profile, and logout actions.

Current location:

```text
laravel-app/
```

### Python Processor

The Python processor is responsible for camera stream processing. It retrieves active camera records from Laravel's internal API, connects to each camera stream URL, detects motion, saves video recordings, and posts saved recording metadata back to Laravel.

Current location:

```text
python-processor/
```

Declared Python dependencies:

```text
opencv-python-headless
numpy
psycopg2-binary
pytest
requests
```

Current Python modules:

- `motion.clip_writer`: buffered clip writing based on the old `KeyClipWriter` prototype.
- `motion.detector`: frame comparison and motion detection.
- `motion.stream_processor`: one-stream orchestration that combines detection and clip writing.
- `services.laravel_api`: small HTTP client for the Laravel processor API.
- `monitor.py`: container entry point that fetches active Laravel cameras, processes streams, and can still process one manual stream through `PROCESSOR_CAMERA_URL`.

### Database

The database stores users, cameras, and video recording metadata. In Docker, the project uses PostgreSQL 16.

Current main entities:

- Users
- Cameras
- Videos

The Docker setup uses two databases:

- `video_monitor`: local development data.
- `video_monitor_test`: Laravel test data, reset by the test suite.

## Functional Requirements

### User Management

When a visitor accesses the system, they are directed to the login page. The login form allows an existing user to authenticate with an email address and password.

If the visitor does not have an account, they can open the registration form and create one by providing:

- Username
- Email address
- Password
- Password confirmation

If a login or registration error occurs, the interface displays an alert with the corresponding error message.

### Camera Management

After authentication, the dashboard and camera pages list cameras registered by the current user.

Each camera row should display:

- Camera name
- Camera stream URL
- Optional location
- Active/inactive status
- Actions to view, edit, and delete the camera

The add camera action opens a form containing:

- Name
- Stream URL
- Optional location
- Active/inactive status

After the user saves the form, the new camera appears in the camera list and can be picked up by the Python processor when active.

The edit camera action opens a form populated with the selected camera data. The user can update:

- Name
- Stream URL
- Optional location
- Active/inactive status

After saving, the camera data is updated in Laravel and becomes available to the processor API.

The delete camera action removes the selected camera from the user's list.

### Video Viewing

When the user opens the video recordings page, the system lists recent recordings for cameras owned by the authenticated user.

Each recording should display:

- Camera
- Filename
- Start time
- Duration
- Motion status
- Action to view the video
- Action to delete the recording

When the user clicks view on a recording, Laravel opens a video detail page with a browser video player, recording metadata, and a download link. New processor recordings are saved as WebM files for browser playback.

### Main Menu

The main menu is located in the authenticated Laravel layout.

The menu includes:

- Dashboard: returns the authenticated user to the summary page.
- Cameras: opens camera management.
- Videos: opens video recording management.
- Profile: opens the Breeze profile page.
- Logout: ends the current session and returns the user to the public authentication flow.

## Motion Detection Flow

The motion detection script should run on the user's computer or in the Python processor container.

Expected processing flow:

1. Retrieve active camera data from Laravel through `GET /api/processor/cameras`.
2. Open each camera stream using its access URL.
3. Capture frames continuously.
4. Resize each frame to 720 pixels.
5. Convert frame coloring as needed for processing.
6. Apply blur with OpenCV to reduce noise.
7. Compare frame differences.
8. Generate a threshold representation containing changed areas.
9. Detect contours in the changed frame areas.
10. Register motion when a contour reaches the configured minimum size.

## Video Recording Flow

When motion is detected, the recording process starts.

Expected recording flow:

1. Start writing frames to a video file.
2. Continue recording while motion is present.
3. Stop recording only after no movement is detected for the configured number of frames.
4. Save the video file as WebM in the processor output directory.
5. Register the recording in Laravel through `POST /api/processor/videos`.
6. Store the camera, filename, public path, timestamps, duration, motion flag, and metadata in Laravel.

## Docker Services

The project defines three Docker services:

```text
laravel-app       Laravel application served by Apache/PHP
python-processor  Python service for video processing
database          PostgreSQL database
```

The Laravel service is built from `docker/laravel/Dockerfile`. It uses Apache with PHP 8.4, enables URL rewriting for Laravel, installs the PostgreSQL PDO extension, includes Composer, and includes Node.js so Breeze/Vite assets can be built inside Docker.

The Laravel container performs first-run application preparation:

- Creates `.env` from `.env.example` if needed.
- Applies Docker database defaults to a newly created `.env`.
- Installs Composer dependencies.
- Generates the Laravel application key if needed.
- Creates the public storage symlink if needed.
- Ensures the separate Laravel test database exists.
- Installs Node dependencies.
- Builds Vite assets.

Database schema creation remains an explicit Laravel migration step:

```bash
docker compose exec laravel-app php artisan migrate
```

The shared Docker volume `shared-videos` is intended to connect generated Python recordings with storage accessible by the Laravel application.

The current integration flow is:

1. Laravel exposes active cameras at `GET /api/processor/cameras`.
2. The Python processor calls that endpoint using the shared `PROCESSOR_API_TOKEN`.
3. Python writes finished clips to `/app/storage/videos`.
4. Docker mounts that same volume into Laravel at `storage/app/public/videos`.
5. Python registers the clip metadata with `POST /api/processor/videos`.
6. Laravel stores a `videos` row containing the camera, filename, public path, timestamps, duration, and metadata.

The public video path stored in the database uses the Laravel storage symlink:

```text
/storage/videos/{filename}
```

New processor recordings use WebM output so the Laravel video page can play them in modern browsers.

## Testing Strategy

The repository currently has two automated test suites:

- Laravel tests, run with Pest/PHPUnit.
- Python processor tests, run with pytest.

Laravel feature tests use `RefreshDatabase`, so they must run against the separate `video_monitor_test` database. The development database is `video_monitor` and should not be reset by tests.

Run Laravel tests:

```bash
docker compose exec laravel-app php artisan test
```

Run Python tests:

```bash
docker compose run --rm python-processor pytest
```

### Local Debugging

The Laravel Docker image includes Xdebug for VS Code step debugging. The project keeps shared editor configuration in `.vscode/launch.json`, mapping the container path `/var/www/html` to the local `laravel-app` folder.

Default Xdebug settings are configured through Docker Compose:

- `XDEBUG_MODE=debug,develop`
- `XDEBUG_START_WITH_REQUEST=yes`
- `XDEBUG_CLIENT_HOST=host.docker.internal`
- `XDEBUG_CLIENT_PORT=9003`

To use the debugger, start the `Listen for Xdebug` launch configuration in VS Code, set a breakpoint, and open the application in the browser.

## Current Data Model

### Cameras

Current fields:

- `id`
- `user_id`
- `name`
- `stream_url`
- `location`
- `is_active`
- `created_at`
- `updated_at`

### Videos

Current fields:

- `id`
- `camera_id`
- `filename`
- `path`
- `started_at`
- `ended_at`
- `duration_seconds`
- `motion_detected`
- `metadata`
- `created_at`
- `updated_at`

## Implementation Status Notes

The current repository already includes the Laravel authentication scaffold, Docker services, a Python processor container, the initial camera/video database tables, and an authenticated dashboard.

Implemented Laravel screens:

- Breeze authentication screens.
- Authenticated camera dashboard with user-scoped camera metrics.
- Authenticated camera list display.
- Authenticated camera create/edit/detail screens.
- Camera create/update/delete actions with validation and user ownership checks.
- Authenticated recent video recording display.
- Authenticated video recording list/detail screens.
- Video recording delete action with user ownership checks.
- Laravel processor API for active camera retrieval and video registration.
- Python processor API client.
- WebM recording output for browser playback.
- Separate PostgreSQL test database.

Planned improvements:

- Demo seed data.
- Multi-camera processor workers.
- Camera processing status and last-error tracking in Laravel.
- Dashboard and table UI polish.
- Screenshots in the README.
- GitHub Actions for Laravel and Python test automation.
