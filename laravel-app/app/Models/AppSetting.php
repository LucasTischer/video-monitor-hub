<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'timezone'])]
class AppSetting extends Model
{
    public const SINGLETON_ID = 1;

    public static function current(): self
    {
        $setting = self::query()->firstOrNew([
            'id' => self::SINGLETON_ID,
        ]);

        $setting->timezone ??= config('app.timezone');

        return $setting;
    }

    public static function currentTimezone(): string
    {
        return self::query()->value('timezone') ?: config('app.timezone');
    }
}
