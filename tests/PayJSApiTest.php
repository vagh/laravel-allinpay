<?php

namespace Vagh\LaravelAllInPay\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Vagh\LaravelAllInPay\AllInPay;
use Vagh\LaravelAllInPay\Exceptions\HttpException;
use Vagh\LaravelAllInPay\Exceptions\InvalidArgumentException;
use Mockery\Matcher\AnyArgs;
use Mockery;

class PayJSApiTest extends TestCase
{
    private $post_params = [
        'open_id'      => 'XJIOWEJCOIEJWOCIJO',
        'notify_url'   => 'http://test.com',
        'app_id'       => '2894723979',
        'amount'       => 300,
        'out_trade_no' => 'XJKIOEWJXIOEJCOIJCOI'
    ];

    /**
     * 测试错误参数
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-25
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetPayJSApiWithInvalidConfig()
    {
        $config = [
            'app_id'      => 'app_id',
            'cus_id'      => 'CJW*(ECHJXS*(JXW)OSJKP',
            'app_version' => NULL
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Param Key:app_version MUST set or can\'t be empty.');

        $pay = new AllInPay($config);

        $params = [];

        $pay->payJSApi($params);

        $this->fail('Failed to assert getPayJSApi throw exception with invalid argument.');
    }

    /**
     * 测试请求异常
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-25
     */
    public function testGetPayJSApiWithGuzzleRuntimeException()
    {
        $config = [
            'app_id'      => 'app_id',
            'cus_id'      => 'CJW*(ECHJXS*(JXW)OSJKP',
            'app_version' => '12',
            'is_test'     => true
        ];

        $client = Mockery::mock(Client::class);

        $client->allows()
            ->request(new AnyArgs())
            ->andThrow(new \Exception('request timeout')); // 当调用 post 方法时会抛出异常。

        $pay = Mockery::mock(AllInPay::class, $config)->makePartial();
        $pay->allows()->getHttpClient()->andReturn($client);

        // 接着需要断言调用时会产生异常。
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');

        $pay->payJSApi($this->post_params);
    }

    /**
     * 测试正常请求
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-25
     */
    public function testGetPayJSApi()
    {
        $config = [
            'app_id'      => 'app_id',
            'cus_id'      => 'CJW*(ECHJXS*(JXW)OSJKP',
            'app_version' => '12',
            'is_test'     => true
        ];

        $response = new Response(200, [], '{"success": true}');
        $client = Mockery::mock(Client::class);

        $client->allows()
            ->request(new AnyArgs())
            ->andReturn($response);

        $pay = Mockery::mock(AllInPay::class, $config)->makePartial();
        $pay->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(['success' => true], $pay->payJSApi($this->post_params));
    }
}