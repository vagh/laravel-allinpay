<?php

/*
 * This file is part of the vagh/laravel-allinpay.
 *
 * (c) VAGH <yu@wowphp.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vagh\LaravelAllInPay;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(AllInPay::class, function () {
            return new AllInPay(config('services.tongLianPay.config'));
        });

        $this->app->alias(AllInPay::class, 'tongLianPay');
    }

    public function provides()
    {
        return [AllInPay::class, 'tongLianPay'];
    }
}
