<?php
// +----------------------------------------------------------------------
// | Custom Curl
// +----------------------------------------------------------------------
// | Copyright (c) 2021 http://233.imjs.work All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://www.gnu.org/licenses/gpl-3.0.html )
// +----------------------------------------------------------------------
// | Author: jshensh <admin@imjs.work>
// +----------------------------------------------------------------------

namespace CustomCurl;

/**
 * Custom Curl 通用工具类
 * @author  jshensh <admin@imjs.work>
 */
class Common
{
    /**
     * 解析 Cookie
     * @access protected
     * @param string $cookie Cookies 字符串
     * @return array
     */
    protected static function parseCookie($cookie) 
    {
        $op = [];
        $pieces = array_filter(array_map('trim', explode(';', $cookie)));
        if (empty($pieces) || !strpos($pieces[0], '=')) {
            return [];
        }
        foreach ($pieces as $part) {
            $cookieParts = explode('=', $part, 2);
            $key = trim($cookieParts[0]);
            $value = isset($cookieParts[1])
                ? trim($cookieParts[1], " \n\r\t\0\x0B")
                : true;
            $op[$key] = $value;
        }
        return $op;
    }

    /**
     * 合并服务器返回的 Cookies 至 Cookie Jar 数组
     * @access protected
     * @param array $jar Cookie Jar 数组
     * @param array $cookies Cookies 数组
     * @return array
     */
    protected static function mergeCookieJar($jar, $cookies) 
    {
        foreach ($cookies as $cookie) {
            if (isset($cookie['expires']) && strtotime($cookie['expires']) - time() <= 0 && isset($jar[key($cookie)])) {
                unset($jar[key($cookie)]);
            } else {
                $jar[key($cookie)] = current($cookie);
            }
        }
        return $jar;
    }
}