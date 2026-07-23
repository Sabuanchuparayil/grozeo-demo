<?php

namespace App\Schedulers;

use App\Models\GodownBoy;
use App\Models\ProcessLock;

class BoyStatusChecker
{
    public function __invoke()
    {
        try
        {
            GodownBoy::where('is_offline', 0)
                ->where(function ($query) {
                    $query->where('latlng_updated_at', '<', now()->subMinutes(60))
                        ->orWhereNull('latlng_updated_at');
                })
                ->update(['is_offline' => 1, 'logout_at' => now(),
                'loggedout_by' => 3]);
            ProcessLock::updateColData("BizAPI_BoyStatusChecker", 0);
        }
        catch (\Exception $e)
        {
            info("BoyStatusChecker ERROR => ".$e->getMessage());
            ProcessLock::updateColData("BizAPI_BoyStatusChecker", 0);
        }
    }
}
