<h1 align="center"> Laravel-AllInPay </h1>

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
use Vagh\LaravelAllInPay\Exceptions\HttpException;
use Vagh\LaravelAllInPay\Exceptions\InvalidArgumentException;
use Vagh\LaravelAllInPay\Exceptions\ServiceException;

$config = [
    'app_id'      => '00000051',
    'cus_id'      => '990440148166000',
    'app_version' => '11',
    'is_test'     => true
];

$pay = new AllInPay($config);

// 测试统一下单(JS SDK)
try {

    $params = [
        'amount'       => '10',
        'out_trade_no' => 'CJXEWIOJOIDUXOUWOEICXNUWEO',
        'open_id'      => 'oTod4wA_AgM40UV2uQ9KJ-sgGmgU',
        'notify_url'   => 'http://test.com',
        'app_id'       => '748923478923'
    ];

    $result = $pay->payJSApi($params);

    var_dump($result);

} catch (Exception $e) {

    if ($e instanceof InvalidArgumentException) {
        $messgae = '参数异常：' . $e->getMessage();
    }
    if ($e instanceof HttpException) {
        $messgae = '通信异常：' . $e->getMessage();
    }
    if ($e instanceof ServiceException) {
        $messgae = '业务逻辑异常：' . $e->getMessage();
    }
    
    var_dump($messgae);
}

// 测试退款
try {
    $params = [
        'amount'           => '10',
        'order_history_id' => 'CJXEWIOJOIDUXOUWOEICXNUWEO',
        'out_trade_no'     => 'oTod4wA_AgM40UV2uQ9KJ-sgGmgU',
    ];

    $result = $pay->refundPay($params);

    var_dump($result);

} catch (Exception $e) {

    if ($e instanceof InvalidArgumentException) {
        $messgae = '参数异常：' . $e->getMessage();
    }
    if ($e instanceof HttpException) {
        $messgae = '通信异常：' . $e->getMessage();
    }
    if ($e instanceof ServiceException) {
        $messgae = '业务逻辑异常：' . $e->getMessage();
    }

    var_dump($messgae);
}
```

---

### 统一支付接口(微信 JS SDK)

**具体参数详情请以及接口返回值请参考通联支付的官方文档**

| 参数  | 说明 | 必传 |
| ------------- | ------------- | ------------- |
| open_id  | 对应微信支付的发起用户ID  | 是 |
| notify_url | 支付回调地址  | 是 |
| app_id | 微信对应的 AppID | 是 |
| amount | 需要支付的金额 单位：分 | 是 |
| out_trade_no | 商户订单号 | 是 |
| valid_time | 订单有效时间 | 否 |
| true_name | 付款人真实姓名 | 否 |
| id_card_no | 证件号 | 否 |

---

### 退款接口

**具体参数详情请以及接口返回值请参考通联支付的官方文档**

| 参数  | 说明 | 必传 |
| ------------- | ------------- | ------------- |
| open_id  | 对应微信支付的发起用户ID  | 是 |
| amount | 需要支付的金额 单位：分 | 是 |
| out_trade_no | 商户订单号 | 是 |
| remark | 备注信息 最多25字 | 否 |


## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/vagh/laravel-allinpay/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/vagh/laravel-allinpay/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT