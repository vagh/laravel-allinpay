<?php

namespace Vagh\LaravelAllInPay;

use Vagh\LaravelAllInPay\Exceptions\InvalidArgumentException;

class RequestTools
{
    /**
     * 生成 Sign
     * @param array $params
     * @param $app_key
     * @return string
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-25
     */
    public static function createSign(array $params, $app_key)
    {
        $params['key'] = $app_key;
        ksort($params);
        $blank_str = self::buildUrlParams($params);
        $sign = md5($blank_str);
        return $sign;
    }

    /**
     * 生成 Url 参数
     * 其实可以考虑使用 http_build_params 函数代替
     * @param array $array
     * @return string
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-25
     */
    public static function buildUrlParams(array $array)
    {
        $buff = "";
        foreach ($array as $k => $v) {
            if ($v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 检测 Sign 是否正确
     * @param array $array
     * @param $app_key
     * @return bool
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-25
     */
    public static function checkSign(array $array, $app_key)
    {
        $sign = $array['sign'];
        unset($array['sign']);
        $array['key'] = $app_key;
        $mySign = self::createSign($array, $app_key);
        return strtolower($sign) == strtolower($mySign);
    }

    /**
     * 验证必传参数
     * @param $needSet
     * @param $params
     * @author yuzhihao <yu@wowphp.com>
     * @since 2019-01-25
     * @throws InvalidArgumentException
     */
    public static function checkMustSetArgs(array $needSet, array $params)
    {
        foreach ($needSet as $item) {
            if (!isset($params[$item]) || empty($params[$item])) {
                throw new InvalidArgumentException('Param Key:' . $item . ' MUST set or can\'t be empty.');
            }
        }
    }
}