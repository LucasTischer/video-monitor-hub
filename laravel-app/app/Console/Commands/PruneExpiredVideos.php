<?php

namespace App\Console\Commands;

use App\Models\Video;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('videos:prune-expired')]
#[Description('Delete expired video recordings according to each camera retention setting.')]
class PruneExpiredVideos extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deletedCount = 0;

        Video::query()
            ->with('camera')
            ->whereHas('camera', function ($query) {
                $query->whereNotNull('recording_retention_days');
            })
            ->lazyById()
            ->each(function (Video $video) use (&$deletedCount): void {
                $retentionDays = $video->camera->recording_retention_days;
                $recordedAt = $video->started_at ?? $video->created_at;

                if (! $recordedAt || $recordedAt->greaterThanOrEqualTo(now()->subDays($retentionDays))) {
                    return;
                }

                $this->deleteRecordingFile($video);
                $video->delete();

                $deletedCount++;
            });

        $this->info("Deleted {$deletedCount} expired video recording(s).");

        return self::SUCCESS;
    }

    private function deleteRecordingFile(Video $video): void
    {
        $path = $this->publicDiskPath($video->path);

        if ($path === null) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function publicDiskPath(string $path): ?string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (! str_starts_with($path, 'videos/')) {
            return null;
        }

        return $path;
    }
}
