<?php

namespace BackOffice\Providers;

use Illuminate\Support\ServiceProvider;

class BackOfficeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/routes.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/drivers.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        
    }
}
