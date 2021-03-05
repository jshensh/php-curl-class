<?php
require('../CustomCurl.php');

$curlObj = CustomCurl::init('http://example.com')
            ->setCurlOpt(CURLOPT_SSL_VERIFYPEER, true)                       // CURLOPT_SSL_VERIFYPEER，默认值 False
            ->setCurlOpt(CURLOPT_SSL_VERIFYHOST, true)                       // CURLOPT_SSL_VERIFYHOST，默认值 False
            ->setCurlOpt(CURLOPT_ENCODING, '')                               // CURLOPT_ENCODING，默认值 ''
            ->setCurlOpt(CURLOPT_SSLCERT, dirname(__FILE__) . '/client.crt') // CURLOPT_SSLCERT，SSL 双向认证证书路径
            ->setCurlOpt(CURLOPT_SSLKEYPASSWD, '')                           // CURLOPT_SSLKEYPASSWD，证书需要的密码
            ->setCurlOpt(CURLOPT_SSLCERTTYPE, 'PEM')                         // CURLOPT_SSLCERTTYPE，证书的类型，支持的格式有 "PEM" (默认值), "DER" 和 "ENG"
            ->setCurlOpt(CURLOPT_SSLKEY, dirname(__FILE__) . '/client.key')  // CURLOPT_SSLKEY，SSL 双向认证证书的私钥
            ->setCurlOpt(CURLOPT_SSLKEYPASSWD, '')                           // CURLOPT_SSLKEYPASSWD，CURLOPT_SSLKEY 私钥的密码
            ->setCurlOpt(CURLOPT_SSLKEYTYPE, 'PEM')                          // CURLOPT_SSLKEYTYPE，私钥的加密类型，支持的密钥类型为 "PEM"(默认值)、"DER" 和 "ENG"
            ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());