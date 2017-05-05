<?php
class CustomCurlStatement
{
    private $ch = null,
            $output = '',
            $body = '',
            $header = '',
            $responseCookies = [],
            $curlErrNo = 0,
            $status = false;

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

    public function getStatus()
    {
        return $this->status;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getCurlErrNo()
    {
        return $this->curlErrNo;
    }

    public function getCookies()
    {
        return $this->responseCookies;
    }
}

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

    private function __construct($url, $method)
    {
        $this->url = $url;
        $method = strtolower($method);
        $this->method = $method === 'get' ? 'get' : 'post';
    }

    public static function init($url, $method = 'get')
    {
        return new self($url, $method);
    }

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

    public function setHeader($k, $v)
    {
        $this->customHeader[] = "{$k}: {$v}";
        return $this;
    }

    public function clearHeaders()
    {
        $this->customHeader = [];
        return $this;
    }

    public function setCookie($k, $v)
    {
        $this->sendCookies[$k] = $v;
        return $this;
    }

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

    public function clearCookies()
    {
        $this->sendCookies = [];
        return $this;
    }

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