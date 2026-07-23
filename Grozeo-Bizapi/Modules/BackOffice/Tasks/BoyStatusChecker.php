<?php

namespace BackOffice\Tasks;

use BackOffice\Models\GodownBoy;

class BoyStatusChecker
{
    public function __invoke()
    {
        try{
        GodownBoy::where('is_offline', 0)
            ->where(function ($query) {
                $query->where('latlng_updated_at', '<', now()->subMinutes(60))
                    ->orWhereNull('latlng_updated_at');
            })
            ->update(['is_offline' => 1, 'logout_at' => now(),
            'loggedout_by' => 3]);

       /* if(date("Hi",time()) >= 2355){
            GodownBoy::where('fcm_id', '!=', '')
            ->where(function ($query) {
                $query->where('login_at', '<', date("Y-m-d",time()) . " 23:55:00" );
            })
            ->update(['is_offline' => 1, 'logout_at' => now(),
            'loggedout_by' => 5, 'fcm_id' => '']);
        }*/
    }catch (\Exception $e)
    {
        info("BOyStatuschecker ERROR => ".$e->getMessage());
    }
    }
}
