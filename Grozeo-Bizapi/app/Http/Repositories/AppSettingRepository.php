<?php

namespace App\Http\Repositories;

use App\Models\AppSetting;

class AppSettingRepository
{
    protected $appSetting;

    public function __construct(AppSetting $appSetting)
    {
        $this->appSetting = $appSetting;
    }

    public function get()
    {
        return $this->appSetting->firstOrFail();
    }
}
