<?php
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
            $ch = null,
            $output = '',
            $body = '',
            $header = '',
            $responseCookies = [],
            $curlErrNo = 0;

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

    public function clearHeader()
    {
        $this->customHeader = [];
        return $this;
    }

    public function setCookies($k, $v)
    {
        $this->sendCookies[$k] = $v;
        return $this;
    }

    public function clearCookies()
    {
        $this->sendCookies = [];
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

    public function exec()
    {
        $this->responseCookies = [];
        $this->header = '';
        $this->body = '';
        $this->output = '';
        $this->curlErrNo = 0;
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
        $this->output = curl_exec($ch);
        $this->ch = $ch;
        $this->curlErrNo = curl_errno($ch);
        if ($this->curlErrNo === 0 || ($this->ignoreCurlError && $output)) {
            $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
            $this->header = substr($this->output, 0, $headerSize);
            $this->body = substr($this->output, $headerSize);
            preg_match_all('/Set-Cookie:(.*?)(\r\n|$)/is', $this->header, $responseCookiesArr);
            if (count($responseCookiesArr[1])) {
                foreach ($responseCookiesArr[1] as $value) {
                    $this->responseCookies[] = $this->parseCookie($value);
                }
            }
            return $this;
        }
        if ($this->reRequest) {
            $this->reRequest--;
            return $this->exec();
        }
        return false;
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

$curlSet = CustomCurl::init('https://www.baidu.com')
            // ->set('postFields', ['a' => 'a', 'array' => ['key' => 'value']])
            ->set('referer', 'http://lab.imjs.work/server.php')
            ->set('ignoreCurlError', 1)
            ->set('timeout', 1)
            ->setHeader('X-Requested-With', 'XMLHttpRequest')
            ->setCookies('a', 'b');

$curlObj = $curlSet->exec();
if ($curlObj) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody());
} else {
    var_dump($curlSet->getCurlErrNo());
}
