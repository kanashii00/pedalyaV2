<?php

namespace App\Observers;

use App\Models\SystemSetting;
use App\Services\CacheRegistry;

class SystemSettingObserver
{
    public function created(SystemSetting $setting): void
    {
        CacheRegistry::bumpSettingsVersion();
    }

    public function updated(SystemSetting $setting): void
    {
        CacheRegistry::bumpSettingsVersion();
    }

    public function deleted(SystemSetting $setting): void
    {
        CacheRegistry::bumpSettingsVersion();
    }

    public function restored(SystemSetting $setting): void
    {
        CacheRegistry::bumpSettingsVersion();
    }
}