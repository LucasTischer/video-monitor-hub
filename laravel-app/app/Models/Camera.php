<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

#[Fillable(['user_id', 'name', 'stream_url', 'location', 'is_active', 'motion_detection_enabled', 'record_after_motion_seconds', 'pre_motion_buffer_seconds', 'monitoring_starts_at', 'monitoring_ends_at', 'recording_retention_days'])]
class Camera extends Model
{
    use HasFactory;

    public const DEFAULT_RECORD_AFTER_MOTION_SECONDS = 2;

    public const DEFAULT_PRE_MOTION_BUFFER_SECONDS = 2;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'motion_detection_enabled' => 'boolean',
            'record_after_motion_seconds' => 'integer',
            'pre_motion_buffer_seconds' => 'integer',
            'recording_retention_days' => 'integer',
        ];
    }

    public function sharedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'camera_shares', 'camera_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('user_id', $user->id)
                ->orWhereHas('sharedUsers', fn (Builder $query) => $query->whereKey($user->id));
        });
    }

    public function scopeCurrentlyMonitorable(Builder $query, ?Carbon $now = null): Builder
    {
        $timezone = AppSetting::currentTimezone();
        $currentTime = ($now ? $now->copy()->setTimezone($timezone) : now($timezone))->format('H:i:s');

        return $query->where(function (Builder $query) use ($currentTime): void {
            $query->where(function (Builder $query): void {
                $query->whereNull('monitoring_starts_at')
                    ->whereNull('monitoring_ends_at');
            })->orWhere(function (Builder $query) use ($currentTime): void {
                $query->whereColumn('monitoring_starts_at', '<', 'monitoring_ends_at')
                    ->where('monitoring_starts_at', '<=', $currentTime)
                    ->where('monitoring_ends_at', '>', $currentTime);
            })->orWhere(function (Builder $query) use ($currentTime): void {
                $query->whereColumn('monitoring_starts_at', '>', 'monitoring_ends_at')
                    ->where(function (Builder $query) use ($currentTime): void {
                        $query->where('monitoring_starts_at', '<=', $currentTime)
                            ->orWhere('monitoring_ends_at', '>', $currentTime);
                    });
            });
        });
    }
}
