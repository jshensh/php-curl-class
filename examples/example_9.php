<?php
class Client extends \CustomCurl\Client
{
    public function sign($mchid, $serialNo, $privateKey)
    {
        $urlParts = parse_url($this->url);
        $canonicalUrl = ($urlParts['path'] . (!empty($urlParts['query']) ? "?{$urlParts['query']}" : ""));
        $timestamp = time();
        $method = strtoupper($this->method);
        $nonce = bin2hex(random_bytes(16));
        $body = $this->conf['postFields'] ? http_build_query($this->conf['postFields']) : '';
        $message = "{$method}\n{$canonicalUrl}\n{$timestamp}\n{$nonce}\n{$body}\n";

        var_dump($message, $privateKey);

        $rawSign = null;
        openssl_sign($message, $rawSign, $privateKey, 'sha256WithRSAEncryption');
        $sign = base64_encode($rawSign);

        $token = sprintf('WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"', $mchid, $nonce, $timestamp, $serialNo, $sign);

        return $this->setHeader('Authorization', $token);
    }
}

$pKey = <<<EOF
-----BEGIN PRIVATE KEY-----
***************************
-----END PRIVATE KEY-----
EOF;

$curlObj = Client::init('https://api.mch.weixin.qq.com/v3/certificates', 'GET')
    ->setHeader('Accept', 'application/json')
    ->sign('166*******', '****************************************', $pKey)
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());