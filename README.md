<h1 align="center"> laravel-allinpay </h1>

<p align="center">针对 <a href="http://www.allinpay.com/">通联支付</a> 接口定制的适用于 Laravel 框架的 SDK</p>

[![Build Status](https://travis-ci.org/vagh/laravel-allinpay.svg?branch=master)](https://travis-ci.org/vagh/laravel-allinpay)

## Installing

```shell
$ composer require vagh/laravel-allinpay -vvv
```

## Usage

在 Laravel 环境中这样使用：

```bash
TODO
```

不在 Laravel 环境下可以这样使用：

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Vagh\LaravelAllInPay\AllInPay;

$config = [
    'app_id'      => '00000051',
    'cus_id'      => '990440148166000',
    'app_version' => '11',
    'is_test'     => true
];

$pay = new AllInPay($config);

try {
    $params = [
        'trxamt'       => '10',
        'out_trade_no' => 'CJXEWIOJOIDUXOUWOEICXNUWEO',
        'open_id'      => 'oTod4wA_AgM40UV2uQ9KJ-sgGmgU',
        'notify_url'   => 'http://test.com',
        'app_id'       => '748923478923'
    ];

    $result = $pay->payJSApi($params);

} catch (Exception $e) {
    var_dump($e->getMessage());
}
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/vagh/laravel-allinpay/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/vagh/laravel-allinpay/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT