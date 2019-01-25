<?php

namespace Vagh\LaravelAllInPay;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(AllInPay::class, function(){
            return new AllInPay(config('services.tongLianPay.config'));
        });

        $this->app->alias(AllInPay::class, 'TongLianPay');
    }

    public function provides()
    {
        return [AllInPay::class, 'TongLianPay'];
    }
}