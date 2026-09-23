<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Services\CacheRegistry;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * Read a setting, caching the value for a short window. The cache is
     * automatically cleared whenever a SystemSetting is created/updated/
     * deleted (see SystemSettingObserver), so writes are immediately visible.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember(CacheRegistry::settingKey($key), CacheRegistry::TTL_SETTINGS, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    public static function setValue(string $key, mixed $value): static
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        CacheRegistry::bumpSettingsVersion();

        return $setting;
    }
}
