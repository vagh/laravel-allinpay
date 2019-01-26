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
use Vagh\LaravelAllInPay\Exceptions\ServiceException;

class RefundPayTest extends TestCase
{
    protected $post_params = [
        'app_id'           => '2894723979',
        'amount'           => 300,
        'out_trade_no'     => 'XJKIOEWJXIOEJCOIJCOI',
        'order_history_id' => 'jIOPXJEWPOJCIXEOPJCP'
    ];

    protected $api_config = [
        'app_id'      => 'app_id',
        'cus_id'      => 'CJW*(ECHJXS*(JXW)OSJKP',
        'app_version' => '12',
        'is_test'     => true
    ];

    /**
     * 测试错误参数
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-26
     * @throws InvalidArgumentException
     */
    public function testRefundPayWithInvalidConfig()
    {
        $config = $this->api_config;

        $config['app_id'] = NULL;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Param Key:app_id MUST set or can\'t be empty.');

        new AllInPay($config);
    }

    /**
     * 测试请求异常
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-26
     */
    public function testRefundPayWithGuzzleRuntimeException()
    {

        $client = Mockery::mock(Client::class);

        $client->allows()
            ->request(new AnyArgs())
            ->andThrow(new \Exception('request timeout')); // 当调用 post 方法时会抛出异常。

        $pay = Mockery::mock(AllInPay::class, $this->api_config)->makePartial();
        $pay->allows()->getHttpClient()->andReturn($client);

        // 接着需要断言调用时会产生异常。
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');

        $pay->refundPay($this->post_params);
    }

    /**
     * 测试正常请求
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-26
     */
    public function testRefundPay()
    {
        $response = new Response(200, [], '{"retcode": "SUCCESS", "trxid" : "JCPEWOJOPXMKOPWKXCLPE"}');
        $client = Mockery::mock(Client::class);

        $client->allows()
            ->request(new AnyArgs())
            ->andReturn($response);

        $pay = Mockery::mock(AllInPay::class, $this->api_config)->makePartial();
        $pay->allows()->getHttpClient()->andReturn($client);

        $this->assertSame([
            'retcode' => 'SUCCESS',
            'trxid'   => 'JCPEWOJOPXMKOPWKXCLPE'
        ], $pay->refundPay($this->post_params));
    }
}