<?php

namespace Vagh\LaravelAllInPay;

class RequestTools
{
    public static function createSign(array $params, $app_key)
    {
        $params['key'] = $app_key;
        ksort($params);
        $blank_str = self::buildUrlParams($params);
        $sign = md5($blank_str);
        return $sign;
    }

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

    public static function checkSign(array $array, $app_key)
    {
        $sign = $array['sign'];
        unset($array['sign']);
        $array['key'] = $app_key;
        $mySign = self::createSign($array, $app_key);
        return strtolower($sign) == strtolower($mySign);
    }
}