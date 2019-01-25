<?php

namespace Vagh\LaravelAllInPay;

use GuzzleHttp\Client;
use Exception;
use Vagh\LaravelAllInPay\Exceptions\HttpException;
use Vagh\LaravelAllInPay\Exceptions\InvalidArgumentException;

class AllInPay
{
    protected $config;
    protected $guzzleOptions = [];
    protected $is_test = false;

    const PAY_API_URL = 'https://vsp.allinpay.com/apiweb/unitorder/pay';
    const TEST_PAY_API_URL = 'https://test.allinpaygd.com/apiweb/unitorder/pay';

    /**
     * AllInPay constructor.
     * @param array $config
     * @throws InvalidArgumentException
     */
    public function __construct(array $config)
    {
        // 检查必要的配置参数
        $this->checkConfig($config);

        $this->config = $config;
        $this->is_test = !!!$config['is_test'];
    }

    /**
     * 微信 JS SDK 支付
     * @param $params
     * @return mixed
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-25
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function payJSApi($params)
    {
        // 验证必传参数
        $must_set = [
            'open_id',
            'notify_url',
            'app_id'
        ];

        RequestTools::checkMustSetArgs($must_set, $params);

        // 转换不规则的命名
        $params_translate = [
            'amount'       => 'trxamt',
            'out_trade_no' => 'reqsn',
            'valid_time'   => 'validtime',
            'true_name'    => 'truename',
            'id_card_no'   => 'idno',
            'open_id'      => 'acct'
        ];
        foreach ($params_translate as $key => $item) {
            if (isset($params[$key])) {
                $params[$item] = $params[$key];
                unset($params[$key]);
            }
        }

        // 规定请求方式是微信JS支付
        $params['paytype'] = 'W02';

        return $this->requestApi(self::TEST_PAY_API_URL, $params);
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
     * @param $url
     * @param $params
     * @return mixed
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-25
     * @throws HttpException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestApi($url, $params)
    {
        $params["cusid"] = $this->config['cus_id'];
        $params["appid"] = $this->config['app_id'];
        $params["version"] = $this->config['app_version'];

        // 计算签名
        $params["sign"] = RequestTools::createSign($params, $this->config['app_id']);
        try {
            $response = $this->getHttpClient()->request('POST', $url, [
                'form_params' => $params
            ])->getBody()->getContents();

            return \json_decode($response, true);

        } catch (Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 检查必要的参数配置
     * @param array $config
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-25
     * @throws InvalidArgumentException
     */
    private function checkConfig(array $config)
    {
        $param_must_set = [
            'app_id',
            'cus_id',
            'app_version',
            'is_test'
        ];

        RequestTools::checkMustSetArgs($param_must_set, $config);
    }

}