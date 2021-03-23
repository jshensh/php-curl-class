<?php
// +----------------------------------------------------------------------
// | Custom Curl
// +----------------------------------------------------------------------
// | Copyright (c) 2020 http://233.imjs.work All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://www.gnu.org/licenses/gpl-3.0.html )
// +----------------------------------------------------------------------
// | Author: jshensh <admin@imjs.work>
// +----------------------------------------------------------------------

namespace CustomCurl;

use CustomCurl\Statement;

/**
 * Custom Curl 结果类
 * @author  jshensh <admin@imjs.work>
 */
class Statement extends Common
{
    private $ch = null,
            $output = '',
            $body = '',
            $header = '',
            $responseCookies = [],
            $curlInfo = [],
            $curlErrNo = 0,
            $status = false;

    /**
     * 构造方法
     * @access public
     * @param int      $curlErrNo Curl 错误码
     * @param resource $ch Curl 句柄
     * @param string   $output 请求结果
     * @param array    &$cookieJarObj Cookie Jar 数组
     * @return void
     */
    public function __construct($curlErrNo, $ch, $output, &$cookieJarObj = []) {
        if ($curlErrNo !== 0) {
            $this->curlErrNo = $curlErrNo;
            return;
        }
        $this->status = true;
        $this->curlInfo = curl_getinfo($ch);
        $headerSize = $this->curlInfo['header_size'];
        $this->header = substr($output, 0, $headerSize);
        $this->body = substr($output, $headerSize);
        preg_match_all('/Set-Cookie:(.*?)(\r\n|$)/is', $this->header, $responseCookiesArr);
        if (count($responseCookiesArr[1])) {
            foreach ($responseCookiesArr[1] as $value) {
                $this->responseCookies[] = self::parseCookie($value);
            }
        }
        $cookieJarObj = self::mergeCookieJar($cookieJarObj, $this->responseCookies);
    }

    /**
     * 获取请求结果状态
     * @access public
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 获取请求结果的 body 部分
     * @access public
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * 获取请求结果的 Header 部分
     * @access public
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * 获取 Curl 错误码
     * @access public
     * @return int
     */
    public function getCurlErrNo()
    {
        return $this->curlErrNo;
    }

    /**
     * 获取请求结果的 Cookies 数组
     * @access public
     * @return array
     */
    public function getCookies()
    {
        return $this->responseCookies;
    }

    /**
     * 获取请求结果的 Info
     * @access public
     * @param string $key Curl Info Key
     * @return mixed
     */
    public function getInfo($key = '')
    {
        return $key ? (isset($this->curlInfo[$key]) ? $this->curlInfo[$key] : false) : $this->curlInfo;
    }
}