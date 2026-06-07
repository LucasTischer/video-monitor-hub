<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'camera_id',
    'filename',
    'path',
    'started_at',
    'ended_at',
    'duration_seconds',
    'motion_detected',
    'metadata',
])]
class Video extends Model
{
    use HasFactory;

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_seconds' => 'integer',
            'motion_detected' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
