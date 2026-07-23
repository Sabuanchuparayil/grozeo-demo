<?php

namespace App\Providers;

use App\Sms\SoftSms;

use App\Sms\TextLocalSms;
use App\Sms\SmsProviderInterFace;
use Illuminate\Support\ServiceProvider;
use App\Modules\Payment\CreateInstamojo;
use App\Modules\Payment\InterfacePayment;
use App\PaymentGateways\InterfacePaymentGateway;
use App\Contracts\PaymentGatewayInterface;

use App\Http\Repositories\Order\OrderRepository;
use App\Http\Repositories\Order\OrderRepositoryInterface;

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

        if (request()->has('payment_gateway') && request('payment_gateway') != "") {
            $defaultPayment = request('payment_gateway');
        }
        
        $this->app->bind(
            PaymentGatewayInterface::class,
            PaytmGateway::class
        );
    
        $this->app->bind(
            SmsProviderInterFace::class,
            config("sms.$defaultSms.class")
        );

        $this->app->bind(
            InterfacePayment::class,
            CreateInstamojo::class
        );
        $this->app->bind(
            InterfacePaymentGateway::class,
            config("paymentgateway.$defaultPayment.class")
        );
        $this->app->bind(OrderRepositoryInterface::class, function ($app) {
        return new OrderRepository(new \App\Models\Order(), new \App\Models\OrderItem());
    });

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
