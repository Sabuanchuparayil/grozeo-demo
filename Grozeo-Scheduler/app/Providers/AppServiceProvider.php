<?php

namespace App\Providers;
use App\Sms\SmsProviderInterFace;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $defaultSms = config('sms.default');
        $defaultPayment = config('paymentgateway.default');
    
        $this->app->bind(
            SmsProviderInterFace::class,
            config("sms.$defaultSms.class")
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        \DB::listen(function ($query) {
            });


    }
}
