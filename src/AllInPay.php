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

use GuzzleHttp\Client;
use Exception;
use Vagh\LaravelAllInPay\Exceptions\HttpException;
use Vagh\LaravelAllInPay\Exceptions\InvalidArgumentException;
use Vagh\LaravelAllInPay\Exceptions\ServiceException;

class AllInPay
{
    protected $config;

    protected $guzzleOptions = [];

    protected $is_test = false;

    const PAY_API_URL = 'https://vsp.allinpay.com/apiweb/unitorder/pay';
    const TEST_PAY_API_URL = 'https://test.allinpaygd.com/apiweb/unitorder/pay';
    const REFUND_PAY_TEST_API_URL = 'https://test.allinpaygd.com/apiweb/unitorder/refund';
    const REFUND_PAY_API_URL = 'https://vsp.allinpay.com/apiweb/unitorder/refund';
    const PAY_QRCODE_TEST_API_URL = 'https://test.allinpaygd.com/apiweb/unitorder/scanqrpay';
    const PAY_QRCODE_API_URL = 'https://vsp.allinpay.com/apiweb/unitorder/scanqrpay';
    const QUERY_ORDER_TEST_API_URL = 'https://test.allinpaygd.com/apiweb/unitorder/query';
    const QUERY_ORDER_API_URL = 'https://vsp.allinpay.com/apiweb/unitorder/query';
    const CANCEL_ORDER_TEST_API_URL = 'https://test.allinpaygd.com/apiweb/unitorder/cancel';
    const CANCEL_ORDER_API_URL = 'https://vsp.allinpay.com/apiweb/unitorder/cancel';

    /**
     * AllInPay constructor.
     *
     * @param array $config
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $config)
    {
        // 检查必要的配置参数
        $this->checkConfig($config);

        $this->config = $config;
        $this->is_test = $config['is_test'] ?: false;
    }

    /**
     * 微信 JS SDK 支付.
     *
     * @param $params
     *
     * @return mixed
     *
     * @author yuzhihao <yu@wowphp.com>
     *
     * @since 2019-01-25
     *
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws ServiceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function payJSApi($params)
    {
        // 验证必传参数
        $must_set = [
            'open_id',
            'notify_url',
            'app_id',
            'amount',
            'out_trade_no',
        ];

        RequestTools::checkMustSetArgs($must_set, $params);

        // 转换不规则的命名
        $params_translate = [
            'amount' => 'trxamt',
            'out_trade_no' => 'reqsn',
            'valid_time' => 'validtime',
            'true_name' => 'truename',
            'id_card_no' => 'idno',
            'open_id' => 'acct',
            'app_id' => 'sub_appid',
        ];
        $params = RequestTools::translateParams($params_translate, $params);

        // 规定请求方式是微信JS支付
        $params['paytype'] = 'W02';

        if ($this->is_test) {
            $api_url = self::TEST_PAY_API_URL;
        } else {
            $api_url = self::PAY_API_URL;
        }

        $response = $this->requestApi($api_url, $params);

        if ('FAIL' === $response['retcode']) {
            throw new ServiceException($response['retmsg'], 7401);
        }

        if ('0000' != $response['trxstatus']) {
            throw new ServiceException($response['errmsg'], 7401);
        }

        return $response;
    }

    /**
     * 付款码支付
     * @param $params
     * @return mixed
     * @author yuzhihao <yu@vagh.cn>
     * @since 2020/5/8
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws ServiceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function scanQrPay($params)
    {
        // 验证必传参数
        $must_set = [
            'auth_code',
            'amount',
            'out_trade_no',
            'order_title',
            'remark'
        ];

        RequestTools::checkMustSetArgs($must_set, $params);

        // 转换不规则的命名
        $params_translate = [
            'amount' => 'trxamt',
            'out_trade_no' => 'reqsn',
            'true_name' => 'truename',
            'id_card_no' => 'idno',
            'auth_code' => 'authcode',
            'order_title' => 'body',
            'remark' => 'remark'
        ];
        $params = RequestTools::translateParams($params_translate, $params);

        if ($this->is_test) {
            $api_url = self::PAY_QRCODE_TEST_API_URL;
        } else {
            $api_url = self::PAY_QRCODE_API_URL;
        }

        $response = $this->requestApi($api_url, $params);

        if ('FAIL' === $response['retcode']) {
            throw new ServiceException($response['retmsg'], 7401);
        }

        return $response;
    }

    /**
     * 查询订单支付状态
     * @param $params
     * @return mixed
     * @author yuzhihao <yu@vagh.cn>
     * @since 2020/5/8
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws ServiceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function queryOrder($params)
    {
        // 验证必传参数
        $must_set = [
            'out_trade_no',
        ];

        RequestTools::checkMustSetArgs($must_set, $params);

        // 转换不规则的命名
        $params_translate = [
            'out_trade_no' => 'reqsn',
            'payment_no' => 'trxid' // 支付的收银宝平台流水
        ];
        $params = RequestTools::translateParams($params_translate, $params);

        if ($this->is_test) {
            $api_url = self::QUERY_ORDER_TEST_API_URL;
        } else {
            $api_url = self::QUERY_ORDER_API_URL;
        }

        $response = $this->requestApi($api_url, $params);

        if ('FAIL' === $response['retcode']) {
            throw new ServiceException($response['retmsg'], 7401);
        }

        return $response;
    }

    /**
     * 撤销支付单
     * 只能撤销当天的交易，全额退款，实时返回退款结果
     * @param $params
     * @return mixed
     * @author yuzhihao <yu@vagh.cn>
     * @since 2020/5/8
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws ServiceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function cancelOrder($params)
    {
        // 验证必传参数
        $must_set = [
            'out_trade_no',
        ];

        RequestTools::checkMustSetArgs($must_set, $params);

        // 转换不规则的命名
        $params_translate = [
            'out_trade_no' => 'oldreqsn',
            'cancel_no' => 'reqsn',
            'cancel_amount' => 'trxamt',
            'payment_no' => 'oldtrxid' // 支付的收银宝平台流水
        ];
        
        $params = RequestTools::translateParams($params_translate, $params);
        
        if ($this->is_test) {
            $api_url = self::CANCEL_ORDER_TEST_API_URL;
        } else {
            $api_url = self::CANCEL_ORDER_API_URL;
        }
        
        $response = $this->requestApi($api_url, $params);

        if ('FAIL' === $response['retcode']) {
            throw new ServiceException($response['retmsg'], 7401);
        }

        return $response;
    }

    /**
     * 交易退款.
     *
     * @param $params
     *
     * @return mixed
     *
     * @author yuzhihao <yu@wowphp.com>
     *
     * @since 2019-01-26
     *
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws ServiceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refundPay($params)
    {
        // 验证必传参数
        $must_set = [
            'order_history_id', // 原交易流水号(通联下单后接口返回的流水号)
            'amount', // 退款金额
            'out_trade_no', // 商户订单号
        ];

        RequestTools::checkMustSetArgs($must_set, $params);

        // 转换不规则的命名
        $params_translate = [
            'amount' => 'trxamt',
            'out_trade_no' => 'reqsn',
            'order_history_id' => 'oldtrxid',
        ];
        $params = RequestTools::translateParams($params_translate, $params);

        if ($this->is_test) {
            $api_url = self::REFUND_PAY_TEST_API_URL;
        } else {
            $api_url = self::REFUND_PAY_API_URL;
        }

        $response = $this->requestApi($api_url, $params);

        if ('FAIL' === $response['retcode']) {
            throw new ServiceException($response['retmsg'], 7401);
        }

        return $response;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     * 发送一个调用 Api Http 的请求
     *
     * @param $url
     * @param $params
     *
     * @return mixed
     *
     * @author yuzhihao <yu@wowphp.com>
     *
     * @since 2019-01-25
     *
     * @throws HttpException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestApi($url, $params)
    {
        $params['cusid'] = $this->config['cus_id'];
        $params['appid'] = $this->config['app_id'];
        $params['version'] = $this->config['app_version'];
        $params['randomstr'] = md5(uniqid(microtime(true), true));

        // 计算签名
        $params['sign'] = RequestTools::createSign($params, $this->config['app_key']);

        try {
            $response = $this->getHttpClient()->request('POST', $url, [
                'form_params' => $params,
            ]);

            return \json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 检查必要的参数配置.
     *
     * @param array $config
     *
     * @author yuzhihao <yu@wowphp.com>
     *
     * @since 2019-01-25
     *
     * @throws InvalidArgumentException
     */
    private function checkConfig(array $config)
    {
        $param_must_set = [
            'app_id',
            'cus_id',
            'app_version',
        ];

        RequestTools::checkMustSetArgs($param_must_set, $config);
    }
}
