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
 * Custom Curl 通用工具类
 * @author  jshensh <jshensh@126.com>
 */
class CustomCurlCommon
{
    /**
     * 解析 Cookie
     * @access private
     * @param string $cookie Cookies 字符串
     * @return array
     */
    private static function parseCookie($cookie) 
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
}

/**
 * Custom Curl 结果类
 * @author  jshensh <jshensh@126.com>
 */
class CustomCurlStatement extends CustomCurlCommon
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
     * @return void
     */
    public function __construct($curlErrNo, $ch, $output) {
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

/**
 * Custom Curl 类
 * @author  jshensh <jshensh@126.com>
 */
class CustomCurl extends CustomCurlCommon
{
    private static $defaultConf = [
                'timeout'         => 5,
                'reRequest'       => 3,
                'maxRedirs'       => 3,
                'ignoreCurlError' => false,
                'followLocation'  => true,
                'referer'         => '',
                'userAgent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
                'customHeader'    => [],
                'sendCookies'     => [],
                'autoRefer'       => 1,
                'postType'        => 'form',
                'proxy'           => '',
                'proxyPort'       => 8080,
                'proxyUserPwd'    => '',
                'proxyType'       => CURLPROXY_HTTP
            ],
            $userConf = [];

    private $url = '',
            $method = '',
            $conf = [
                'postFields' => []
            ];

    /**
     * 构造方法
     * @access private
     * @param string $url URL 字符串
     * @param string $method 请求方法，[get, post, put, delete]
     * @return void
     */
    private function __construct($url, $method)
    {
        $this->url = $url;
        $method = strtolower($method);
        $this->conf = array_merge(self::$defaultConf, self::$userConf);
        $this->method = in_array($method, ['get', 'post', 'put', 'delete']) ? $method : 'get';
    }

    /**
     * 设置默认配置
     * @access public
     * @param string $k 配置 Key
     * @param string $v 配置 Value
     * @return bool
     */
    public static function setConf($k, $v)
    {
        if (!isset(self::$defaultConf[$k])) {
            return false;
        }
        if ($k === 'sendCookies') {
            if (is_string($v)) {
                $v = self::parseCookie($v);
            }
            if (is_array($v)) {
                if (count($v) !== count($v, 1)) {
                    return false;
                }
            } else {
                return false;
            }
        }
        self::$userConf[$k] = $v;
        return true;
    }

    /**
     * 恢复默认配置
     * @access public
     * @param string $k 配置 Key
     * @return bool
     */
    public static function resetConf($k = null)
    {
        if ($k) {
            if (!isset(self::$userConf[$k])) {
                return false;
            }
            unset(self::$userConf[$k]);
            return true;
        }
        self::$userConf = [];
        return true;
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
                if (!in_array($this->method, ['post', 'put'])) {
                    return $this;
                }
                break;
            case 'postType':
                $v = strtolower($v);
                if (!in_array($v, ['string', 'form', 'json'])) {
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
            case 'proxy':
                // no break
            case 'proxyPort':
                // no break
            case 'proxyUserPwd':
                // no break
            case 'proxyType':
                // no break
            case 'userAgent':
                break;
            default:
                return $this;
        }
        $this->conf[$k] = $v;
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
        $this->conf['customHeader'][] = "{$k}: {$v}";
        return $this;
    }

    /**
     * 清空设置的 Header
     * @access public
     * @return CustomCurl
     */
    public function clearHeaders()
    {
        $this->conf['customHeader'] = [];
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
        $this->conf['sendCookies'][$k] = $v;
        return $this;
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
        $cookies = is_array($parm) ? $parm : self::parseCookie($parm);
        $cookiesC = count($cookies);

        if (!$cookiesC || ($cookiesC !== count($cookies, 1))) {
            return $this;
        }

        if ($append) {
            $this->conf['sendCookies'] = array_merge($this->conf['sendCookies'], $cookies);
        } else {
            $this->conf['sendCookies'] = $cookies;
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
        $this->conf['sendCookies'] = [];
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
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->conf['timeout']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_REFERER, $this->conf['referer']);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->conf['userAgent']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->conf['followLocation']);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $this->conf['maxRedirs']);
        curl_setopt($ch, CURLOPT_AUTOREFERER, $this->conf['autoRefer']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
        if (count($this->conf['sendCookies'])) {
            $sendCookies = '';
            foreach ($this->conf['sendCookies'] as $key => $value) {
                $sendCookies .= "{$key}={$value}; ";
            }
            curl_setopt($ch, CURLOPT_COOKIE, $sendCookies);
        }
        if (in_array($this->method, ['post', 'put'])) {
            if ($this->conf['postType'] === 'json') {
                $postJsonData = json_encode($this->conf['postFields']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postJsonData);
                $this->conf['customHeader'][] = 'Content-Type: application/json';
                $this->conf['customHeader'][] = 'Content-Length: ' . strlen($postJsonData);
            } else if ($this->conf['postType'] === 'form') {
                if (is_array($this->conf['postFields'])) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->conf['postFields']));
                }
            } else {
                if (is_string($this->conf['postFields'])) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->conf['postFields']);
                    $this->conf['customHeader'][] = 'Content-Type: text/plain';
                    $this->conf['customHeader'][] = 'Content-Length: ' . strlen($this->conf['postFields']);
                }
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->conf['customHeader']);
        if ($this->conf['proxy']) {
            curl_setopt($ch, CURLOPT_PROXY, $this->conf['proxy']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->conf['proxyPort']);
            if ($this->conf['proxyUserPwd']) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->conf['proxyUserPwd']);
            }
            curl_setopt($ch, CURLOPT_PROXYTYPE, $this->conf['proxyType']);
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        $output = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        if ($curlErrNo === 0 || ($this->conf['ignoreCurlError'] && $output)) {
            return new CustomCurlStatement(0, $ch, $output);
        }
        $this->conf['reRequest']--;
        if ($this->conf['reRequest'] > 0) {
            return $this->exec();
        }
        return new CustomCurlStatement($curlErrNo, $ch, $output);
    }
}