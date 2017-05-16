<?php
// +----------------------------------------------------------------------
// | Custom Curl
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://233.imjs.work All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: jshensh <jshensh@126.com>
// +----------------------------------------------------------------------

/**
 * Custom Curl 结果类
 * @author  jshensh <jshensh@126.com>
 */
class CustomCurlStatement
{
    private $ch = null,
            $output = '',
            $body = '',
            $header = '',
            $responseCookies = [],
            $curlErrNo = 0,
            $status = false;

    /**
     * 解析 Cookie
     * @access private
     * @param string $cookie Cookies 字符串
     * @return array
     */
    private function parseCookie($cookie) 
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
     * 构造方法
     * @access public
     * @param int      $curlErrNo Curl 错误码
     * @param resource $ch Curl 句柄
     * @param string   $output 请求结果
     * @return void
     */
    public function __construct($curlErrNo, $ch, $output) {
        if ($curlErrNo !== 0) {
            $this->curlErrNo = $curlErrNo;
            return;
        }
        $this->status = true;
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->header = substr($output, 0, $headerSize);
        $this->body = substr($output, $headerSize);
        preg_match_all('/Set-Cookie:(.*?)(\r\n|$)/is', $this->header, $responseCookiesArr);
        if (count($responseCookiesArr[1])) {
            foreach ($responseCookiesArr[1] as $value) {
                $this->responseCookies[] = $this->parseCookie($value);
            }
        }
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
}

/**
 * Custom Curl 类
 * @author  jshensh <jshensh@126.com>
 */
class CustomCurl
{
    private $url = '',
            $timeout = 5,
            $reRequest = 3,
            $maxRedirs = 3,
            $method = '',
            $postFields = [],
            $ignoreCurlError = false,
            $followLocation = true,
            $referer = '',
            $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
            $customHeader = [],
            $sendCookies = [],
            $autoRefer = 1;

    /**
     * 构造方法
     * @access private
     * @param string $url URL 字符串
     * @param string $method 请求方法，post 或者 get
     * @return void
     */
    private function __construct($url, $method)
    {
        $this->url = $url;
        $method = strtolower($method);
        $this->method = $method === 'get' ? 'get' : 'post';
    }

    /**
     * 初始化
     * @access public
     * @param string $url URL 字符串
     * @param string $method 请求方法，post 或者 get
     * @return CustomCurl
     */
    public static function init($url, $method = 'get')
    {
        return new self($url, $method);
    }

    /**
     * 设置项
     * @access public
     * @param string $k 设置项 Key
     * @param string $v 设置项 Value
     * @return CustomCurl
     */
    public function set($k, $v)
    {
        switch ($k) {
            case 'timeout':
                // no break
            case 'maxRedirs':
                // no break
            case 'reRequest':
                if ($v < 0 || !is_numeric($v)) {
                    return $this;
                }
                break;
            case 'postFields':
                if ($this->method !== "post" || !is_array($v)) {
                    return $this;
                }
                break;
            case 'ignoreCurlError':
                // no break
            case 'followLocation':
                // no break
            case 'autoRefer':
                $v = (bool)$v;
                break;
            case 'referer':
                // no break
            case 'userAgent':
                break;
            default:
                return $this;
        }
        $this->$k = $v;
        return $this;
    }

    /**
     * 设置 Header
     * @access public
     * @param string $k Header Key
     * @param string $v Header Value
     * @return CustomCurl
     */
    public function setHeader($k, $v)
    {
        $this->customHeader[] = "{$k}: {$v}";
        return $this;
    }

    /**
     * 清空设置的 Header
     * @access public
     * @return CustomCurl
     */
    public function clearHeaders()
    {
        $this->customHeader = [];
        return $this;
    }

    /**
     * 设置 Cookie
     * @access public
     * @param string $k Cookie Key
     * @param string $v Cookie Value
     * @return CustomCurl
     */
    public function setCookie($k, $v)
    {
        $this->sendCookies[$k] = $v;
        return $this;
    }

    /**
     * 解析 Cookie
     * @access private
     * @param string $cookie Cookies 字符串
     * @return array
     */
    private function parseCookie($cookie) 
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
     * 设置 Cookies
     * @access public
     * @param string|array  $parm Cookies 字符串或一维数组
     * @param bool          $append 是否追加设置
     * @return CustomCurl
     */
    public function setCookies($parm, $append = false)
    {
        $cookies = is_array($parm) ? $parm : $this->parseCookie($parm);
        $cookiesC = count($cookies);

        if (!$cookiesC || ($cookiesC !== count($cookies, 1))) {
            return $this;
        }

        if ($append) {
            $this->sendCookies = array_merge($this->sendCookies, $cookies);
        } else {
            $this->sendCookies = $cookies;
        }

        return $this;
    }

    /**
     * 清空 Cookies
     * @access public
     * @return CustomCurl
     */
    public function clearCookies()
    {
        $this->sendCookies = [];
        return $this;
    }

    /**
     * 执行 Curl
     * @access public
     * @return CustomCurlStatement
     */
    public function exec()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->customHeader);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->followLocation);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $this->maxRedirs);
        curl_setopt($ch, CURLOPT_AUTOREFERER, $this->autoRefer);
        if (count($this->sendCookies)) {
            $sendCookies = "";
            foreach ($this->sendCookies as $key => $value) {
                $sendCookies .= "{$key}={$value}; ";
            }
            curl_setopt($ch, CURLOPT_COOKIE, $sendCookies);
        }
        if ($this->method === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->postFields));
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        $output = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        if ($curlErrNo === 0 || ($this->ignoreCurlError && $output)) {
            return new CustomCurlStatement(0, $ch, $output);
        }
        $this->reRequest--;
        if ($this->reRequest > 0) {
            return $this->exec();
        }
        return new CustomCurlStatement($curlErrNo, $ch, $output);
    }
}