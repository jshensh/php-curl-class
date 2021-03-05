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

/**
 * Custom Curl 通用工具类
 * @author  jshensh <admin@imjs.work>
 */
class CustomCurlCommon
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

/**
 * Custom Curl 结果类
 * @author  jshensh <admin@imjs.work>
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

/**
 * Custom Curl 类
 * @author  jshensh <admin@imjs.work>
 */
class CustomCurl extends CustomCurlCommon
{
    private static $defaultConf = [
                'timeout'              => 5,
                'reRequest'            => 3,
                'maxRedirs'            => 3,
                'ignoreCurlError'      => false,
                'followLocation'       => false,
                'referer'              => '',
                'userAgent'            => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36',
                'customHeader'         => [],
                'sendCookies'          => [],
                'autoRefer'            => 1,
                'postType'             => 'form',
                'proxy'                => '',
                'proxyPort'            => 8080,
                'proxyUserPwd'         => '',
                'proxyType'            => CURLPROXY_HTTP,
                'postFieldsBuildQuery' => true
            ],
            $defaultCurlopt = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_ENCODING       => ''
            ],
            $userConf = [],
            $userCurlopt = [];

    private $url = '',
            $method = '',
            $conf = [
                'postFields' => []
            ],
            $curlopt = [],
            $cookieJarObj = [];

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
        $this->curlopt = self::$defaultCurlopt;
        foreach (self::$userCurlopt as $key => $value) {
            $this->curlopt[$key] = $value;
        }
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
     * 设置默认 CurlOpt
     * @access public
     * @param string $k 配置 Key
     * @param string $v 配置 Value
     * @return bool
     */
    public static function setCurlOptConf($k, $v)
    {
        if (!isset(self::$defaultCurlopt[$k])) {
            return false;
        }
        self::$userCurlopt[$k] = $v;
        return true;
    }

    /**
     * 恢复默认 CurlOpt
     * @access public
     * @param string $k 配置 Key
     * @return bool
     */
    public static function resetCurlOptConf($k = null)
    {
        if ($k) {
            if (!isset(self::$userCurlopt[$k])) {
                return false;
            }
            unset(self::$userCurlopt[$k]);
            return true;
        }
        self::$userCurlopt = [];
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
                // no break
            case 'postFieldsBuildQuery':
                break;
            default:
                return $this;
        }
        $this->conf[$k] = $v;
        return $this;
    }

    /**
     * 设置 CurlOpt
     * @access public
     * @param string $k 设置项 Key
     * @param string $v 设置项 Value
     * @return CustomCurl
     */
    public function setCurlOpt($k, $v)
    {
        switch ($k) {
            case CURLOPT_SSL_VERIFYPEER:
                // no break
            case CURLOPT_SSL_VERIFYHOST:
                if (!is_bool($v)) {
                    return $this;
                }
                break;
            case CURLOPT_ENCODING:
                // no break
            case CURLOPT_SSLCERT:
                // no break
            case CURLOPT_SSLCERTPASSWD:
                // no break
            case CURLOPT_SSLCERTTYPE:
                // no break
            case CURLOPT_SSLKEY:
                // no break
            case CURLOPT_SSLKEYPASSWD:
                // no break
            case CURLOPT_SSLKEYTYPE:
                if (!is_string($v)) {
                    return $this;
                }
                break;
            default:
                return $this;
        }
        $this->curlopt[$k] = $v;
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

        if (!$cookiesC || $cookiesC !== count($cookies, 1)) {
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
     * 设置 Cookie Jar
     * @access public
     * @param array &$jar Cookie Jar 数组
     * @return CustomCurl
     */
    public function cookieJar(&$jar)
    {
        if (!is_array($jar) || count($jar) !== count($jar, 1)) {
            return $this;
        }

        $this->cookieJarObj = &$jar;
        return $this->setCookies($this->cookieJarObj, true);
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
        curl_setopt($ch, CURLOPT_REFERER, $this->conf['referer']);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->conf['userAgent']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->conf['followLocation']);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $this->conf['maxRedirs']);
        curl_setopt($ch, CURLOPT_AUTOREFERER, $this->conf['autoRefer']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
        if (count($this->conf['sendCookies'])) {
            $this->cookieJarObj = $this->conf['sendCookies'];
            $sendCookies = '';
            foreach ($this->conf['sendCookies'] as $key => $value) {
                $sendCookies .= "{$key}={$value}; ";
            }
            curl_setopt($ch, CURLOPT_COOKIE, $sendCookies);
        }
        if (in_array($this->method, ['post', 'put'])) {
            if ($this->conf['postType'] === 'json') {
                if (is_string($this->conf['postFields'])) {
                    $postJsonData = $this->conf['postFields'];
                } else {
                    $postJsonData = json_encode($this->conf['postFields']);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postJsonData);
                $this->conf['customHeader'][] = 'Content-Type: application/json';
                $this->conf['customHeader'][] = 'Content-Length: ' . strlen($postJsonData);
            } else if ($this->conf['postType'] === 'form') {
                if (is_array($this->conf['postFields'])) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->conf['postFieldsBuildQuery'] ? http_build_query($this->conf['postFields']) : $this->conf['postFields']);
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
        foreach ($this->curlopt as $key => $value) {
            curl_setopt($ch, $key, $value);
        }
        $output = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        if ($curlErrNo === 0 || ($this->conf['ignoreCurlError'] && $output)) {
            return new CustomCurlStatement(0, $ch, $output, $this->cookieJarObj);
        }
        $this->conf['reRequest']--;
        if ($this->conf['reRequest'] > 0) {
            return $this->exec();
        }
        return new CustomCurlStatement($curlErrNo, $ch, $output);
    }
}
