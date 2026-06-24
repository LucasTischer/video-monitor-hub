# Project Documentation

## Overview

Video Monitor Hub is designed to help users manage camera streams and review videos recorded when motion is detected. The system has three main runtime parts:

- A Laravel web application for authentication, camera management, recording listing, and video playback.
- A Python processor that uses OpenCV to detect motion, record videos, and register recordings through Laravel's processor API.
- A PostgreSQL database used by Laravel for users, app settings, cameras, sharing rules, and recording metadata.

## System Components

### Laravel Application

The Laravel application is responsible for the user interface and database-backed management features. It handles:

- User authentication.
- Admin-only application settings.
- Camera CRUD operations.
- Camera sharing and role-based access control.
- Listing recent motion recordings.
- Playing recorded videos in the browser.
- Navigation between the dashboard, camera management, video management, settings, profile, and logout actions.

Current location:

```text
laravel-app/
```

### Python Processor

The Python processor is responsible for camera stream processing. It retrieves currently processable camera records from Laravel's internal API, connects to each camera stream URL, detects motion, saves video recordings, and posts saved recording metadata back to Laravel.

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
- `workers.camera_worker`: one-camera worker thread responsible for stream processing and video registration.
- `workers.supervisor`: keeps one worker running for each active camera.
- `monitor.py`: container entry point that refreshes active Laravel cameras, syncs workers, and can still process one manual stream through `PROCESSOR_CAMERA_URL`.

### Database

The database stores users, app settings, cameras, camera sharing rules, and video recording metadata. In Docker, the project uses PostgreSQL 16.

Current main entities:

- Users
- App settings
- Cameras
- Camera shares
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

Users can have the `is_admin` flag. Admin users can access the Settings page and update the global application timezone. The demo user is seeded as an admin so a fresh local installation has an account that can manage app settings.

### Application Settings

Global application settings are stored in `app_settings`. The current setting is:

```text
timezone  Timezone used by monitoring windows and processor timestamps
```

When no database setting exists, Laravel falls back to `APP_TIMEZONE` from configuration. In local Docker development this defaults to `America/Sao_Paulo`.

### Camera Management

After authentication, the dashboard and camera pages list cameras owned by the current user and cameras shared with the current user.

Each camera row should display:

- Camera name
- Camera stream URL
- Optional location
- Active/inactive status
- Actions to view, edit, delete, and manage sharing when allowed by the user's role

The add camera action opens a form containing:

- Name
- Stream URL
- Optional location
- Active/inactive status
- Motion detection enabled/disabled
- Recording retention
- Seconds to keep recording after motion stops
- Pre-motion buffer seconds
- Recording resolution
- Recording FPS
- Optional daily monitoring start and end time

After the user saves the form, the new camera appears in the camera list and can be picked up by the Python processor when active.

The edit camera action opens a form populated with the selected camera data. The user can update:

- Name
- Stream URL
- Optional location
- Active/inactive status
- Motion detection enabled/disabled
- Recording retention
- Seconds to keep recording after motion stops
- Pre-motion buffer seconds
- Recording resolution
- Recording FPS
- Optional daily monitoring start and end time

After saving, the camera data is updated in Laravel and becomes available to the processor API.

The delete camera action removes the selected camera and its related videos/shares when the user is allowed to delete it.

Camera timing settings:

```text
recording_retention_days       null keeps recordings forever; N deletes recordings older than N days.
record_after_motion_seconds    Seconds to continue recording after motion stops.
pre_motion_buffer_seconds      Seconds of buffered frames to include before detected motion.
recording_resolution_height    Optional output height; null keeps the original source resolution.
recording_fps                  Output video frame rate used by the generated clip.
monitoring_starts_at           Optional daily monitoring window start time.
monitoring_ends_at             Optional daily monitoring window end time.
```

Both monitoring window fields must be filled together or left blank together. Blank monitoring window values mean the camera can be monitored all day. A start time later than the end time represents an overnight window, such as `22:00` to `06:00`.

### Camera Sharing

Each camera has one owner through `cameras.user_id`. Owners can share cameras with other registered users from the camera detail page.

The current share roles are:

```text
viewer   Can view camera details and recordings.
editor   Can view camera details/recordings and edit camera settings.
manager  Can view, edit, delete, and manage shared access.
```

Camera shares are stored in `camera_shares` with the shared user's role. A camera can only be shared once with the same user. Owners do not need a `camera_shares` row because ownership already gives full access.

Camera policies are responsible for deciding whether the authenticated user can view, update, delete, or share a camera. Video policies defer to the related camera, so recordings inherit the camera's permissions.

### Video Viewing

When the user opens the video recordings page, the system lists recent recordings for cameras visible to the authenticated user. This includes owned cameras and cameras shared with the user.

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
- Settings: opens admin-only application settings.
- Profile: opens the Breeze profile page.
- Logout: ends the current session and returns the user to the public authentication flow.

## Motion Detection Flow

The motion detection script should run on the user's computer or in the Python processor container.

Expected processing flow:

1. Retrieve currently eligible camera data from Laravel through `GET /api/processor/cameras`.
2. Start one camera worker for each active camera that is not already running.
3. Stop workers for cameras that are no longer returned by Laravel or whose configuration changed.
4. Open each camera stream using its access URL.
5. Capture frames continuously.
6. Resize each frame to 720 pixels.
7. Convert frame coloring as needed for processing.
8. Apply blur with OpenCV to reduce noise.
9. Compare frame differences.
10. Generate a threshold representation containing changed areas.
11. Detect contours in the changed frame areas.
12. Register motion when a contour reaches the configured minimum size.

## Video Recording Flow

When motion is detected, the recording process starts.

Expected recording flow:

1. Start writing buffered pre-motion frames and live frames to a video file.
2. Continue recording while motion is present.
3. Stop recording only after no movement is detected for the configured `record_after_motion_seconds`.
4. Save the video file as WebM in the processor output directory.
5. Register the recording in Laravel through `POST /api/processor/videos`.
6. Store the camera, filename, public path, timezone-aware timestamps, duration, motion flag, and metadata in Laravel.

The processor receives the application timezone from Laravel and uses it when creating clip filenames and `started_at` / `ended_at` metadata.

## Docker Services

The project defines four Docker services:

```text
laravel-app       Laravel application served by Apache/PHP
laravel-scheduler Laravel scheduler worker for recurring Artisan tasks
python-processor  Python service for video processing
database          PostgreSQL database
```

The Laravel service is built from `docker/laravel/Dockerfile`. It uses Apache with PHP 8.4, enables URL rewriting for Laravel, installs the PostgreSQL PDO extension, includes Composer, and includes Node.js so Breeze/Vite assets can be built inside Docker.

The scheduler service uses the same Laravel image and mounted application code, but runs `php artisan schedule:work` instead of Apache. It is responsible for recurring Artisan tasks such as pruning expired video recordings.

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
./bin/artisan migrate
```

Demo data can be created with:

```bash
./bin/artisan db:seed
```

Artisan commands should be run through `./bin/artisan` during local development.
The wrapper executes the command inside the Laravel container as the host user,
which prevents generated files from being owned by the container user.

The default seeder creates an admin demo user, inactive demo cameras, playable WebM recording placeholders, and recording metadata. Demo cameras are inactive by default so the processor does not attempt to connect to placeholder stream URLs.

The shared Docker volume `shared-videos` is intended to connect generated Python recordings with storage accessible by the Laravel application.

The current integration flow is:

1. Laravel exposes currently processable cameras at `GET /api/processor/cameras`.
2. The Python processor calls that endpoint using the shared `PROCESSOR_API_TOKEN`.
3. Python starts, stops, or restarts camera workers to match the returned camera list and settings.
4. Each worker writes finished clips to `/app/storage/videos` using the camera's recording resolution and FPS settings.
5. Docker mounts that same volume into Laravel at `storage/app/public/videos`.
6. Python registers the clip metadata with `POST /api/processor/videos`.
7. Laravel stores a `videos` row containing the camera, filename, public path, timestamps, duration, and metadata.

The public video path stored in the database uses the Laravel storage symlink:

```text
/storage/videos/{filename}
```

New processor recordings use WebM output so the Laravel video page can play them in modern browsers.

### Scheduled Cleanup

Laravel schedules the `videos:prune-expired` command daily. The command checks each video's related camera retention setting:

```text
recording_retention_days = null  Keep recordings forever.
recording_retention_days = N     Delete recordings older than N days.
```

When a recording expires, Laravel deletes both the `videos` database row and the file from the public storage disk.

In Docker, the `laravel-scheduler` service keeps the scheduler running with `php artisan schedule:work`.

## Testing Strategy

The repository currently has two automated test suites:

- Laravel tests, run with Pest/PHPUnit.
- Python processor tests, run with pytest.

Laravel feature tests use `RefreshDatabase`, so they must run against the separate `video_monitor_test` database. The development database is `video_monitor` and should not be reset by tests.

Run Laravel tests:

```bash
./bin/artisan test
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

### Users

Current fields:

- `id`
- `name`
- `email`
- `email_verified_at`
- `password`
- `is_admin`
- `remember_token`
- `created_at`
- `updated_at`

### App Settings

Current fields:

- `id`
- `timezone`
- `created_at`
- `updated_at`

`app_settings.timezone` stores the global application timezone. If the setting is missing, Laravel falls back to `APP_TIMEZONE`.

### Cameras

Current fields:

- `id`
- `user_id`
- `name`
- `stream_url`
- `location`
- `is_active`
- `motion_detection_enabled`
- `record_after_motion_seconds`
- `pre_motion_buffer_seconds`
- `recording_resolution_height`
- `recording_fps`
- `monitoring_starts_at`
- `monitoring_ends_at`
- `recording_retention_days`
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

### Camera Shares

Current fields:

- `id`
- `camera_id`
- `user_id`
- `role`
- `created_at`
- `updated_at`

The table enforces a unique `camera_id` and `user_id` pair so the same camera cannot be shared with the same user more than once.

## Implementation Status Notes

The current repository already includes the Laravel authentication scaffold, Docker services, a Python processor container, the current application database tables, and an authenticated dashboard.

Implemented Laravel screens:

- Breeze authentication screens.
- Authenticated camera dashboard with owned and shared camera metrics.
- Authenticated camera list display for owned and shared cameras.
- Authenticated camera create/edit/detail screens.
- Camera create/update/delete actions with validation and policy checks.
- Camera sharing create/update/delete actions with validation and policy checks.
- Authenticated recent video recording display for owned and shared cameras.
- Authenticated video recording list/detail screens.
- Video recording delete action with camera-inherited policy checks.
- Laravel processor API for active camera retrieval and video registration.
- Python processor API client.
- Multi-camera processor workers.
- WebM recording output for browser playback.
- Camera-level recording retention and scheduled expired recording cleanup.
- Camera-level motion detection enable/disable.
- Camera-level pre-motion buffer and record-after-motion timing.
- Camera-level recording resolution and FPS settings.
- Camera-level daily monitoring windows.
- Admin users and admin-only global timezone settings.
- Processor timezone-aware filenames and recording timestamps.
- Separate PostgreSQL test database.
- Demo seed data with a demo user, cameras, and playable placeholder recordings.

Planned improvements:

- Camera processing status and last-error tracking in Laravel.
- Screenshots in the README.
- GitHub Actions for Laravel and Python test automation.
