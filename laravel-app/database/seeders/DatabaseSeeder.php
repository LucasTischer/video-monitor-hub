<?php

namespace Database\Seeders;

use App\Models\Camera;
use App\Models\User;
use App\Models\Video;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
            ],
        );

        $cameras = collect([
            [
                'name' => 'Front Gate',
                'stream_url' => 'http://demo-camera.local/front-gate',
                'location' => 'Main entrance',
            ],
            [
                'name' => 'Garage',
                'stream_url' => 'http://demo-camera.local/garage',
                'location' => 'Vehicle access',
            ],
            [
                'name' => 'Back Yard',
                'stream_url' => 'http://demo-camera.local/back-yard',
                'location' => 'Rear perimeter',
            ],
        ])->map(fn (array $camera) => Camera::updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => $camera['name'],
            ],
            [
                'stream_url' => $camera['stream_url'],
                'location' => $camera['location'],
                'is_active' => false,
            ],
        ));

        $startedAt = CarbonImmutable::now()->subHours(3);

        $recordings = [
            [
                'camera' => 'Front Gate',
                'filename' => 'demo-front-gate-motion.webm',
                'started_at' => $startedAt,
                'duration_seconds' => 18,
                'largest_contour_area' => 2160,
            ],
            [
                'camera' => 'Garage',
                'filename' => 'demo-garage-motion.webm',
                'started_at' => $startedAt->addMinutes(47),
                'duration_seconds' => 12,
                'largest_contour_area' => 1480,
            ],
            [
                'camera' => 'Back Yard',
                'filename' => 'demo-back-yard-motion.webm',
                'started_at' => $startedAt->addMinutes(96),
                'duration_seconds' => 24,
                'largest_contour_area' => 3120,
            ],
        ];

        foreach ($recordings as $recording) {
            $camera = $cameras->firstWhere('name', $recording['camera']);
            $this->writeDemoRecording($recording['filename']);

            Video::updateOrCreate(
                [
                    'camera_id' => $camera->id,
                    'filename' => $recording['filename'],
                ],
                [
                    'path' => "/storage/videos/{$recording['filename']}",
                    'started_at' => $recording['started_at'],
                    'ended_at' => $recording['started_at']->addSeconds($recording['duration_seconds']),
                    'duration_seconds' => $recording['duration_seconds'],
                    'motion_detected' => true,
                    'metadata' => [
                        'source' => 'database-seeder',
                        'demo' => true,
                        'largest_contour_area' => $recording['largest_contour_area'],
                    ],
                ],
            );
        }
    }

    private function writeDemoRecording(string $filename): void
    {
        $directory = storage_path('app/public/videos');
        $path = "{$directory}/{$filename}";

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, base64_decode($this->demoWebmBase64(), true));
    }

    private function demoWebmBase64(): string
    {
        return <<<'WEBM'
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
WEBM;
    }
}
