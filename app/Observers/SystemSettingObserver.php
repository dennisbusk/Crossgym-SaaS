<?php

namespace App\Observers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingObserver {

    public function updated( SystemSetting $systemSetting ): void {
        $this->clearCache($systemSetting->key);
    }

    public function deleted( SystemSetting $systemSetting ): void {
        $this->clearCache($systemSetting->key);
    }

    public function restored( SystemSetting $systemSetting ): void {
        $this->clearCache($systemSetting->key);
    }

    public function clearCache($key): void {
Cache::forget('system_settings.'.$key);
    }
}
