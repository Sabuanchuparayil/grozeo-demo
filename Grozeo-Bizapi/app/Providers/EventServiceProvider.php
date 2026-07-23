<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    // protected $listen = [
    //     Registered::class => [
    //         SendEmailVerificationNotification::class,
    //     ],
    // ];
    protected $listen = [
        'App\Events\OtpGenerated' => [
            'App\Listeners\SendOtp',
        ],
        'App\Events\Searched' => [
            'App\Listeners\AddSearchQueryToDb',
        ],
        'App\Events\OrderHistory' => [
            'App\Listeners\OrderHistoryListener',
        ],
        'App\Events\DelayedOrderActions' => [
            'App\Listeners\DelayedOrderActionsListener',
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
