<?php
use CustomCurl\Client;

$curlObj = Client::init('http://cn.bing.com/search?q=php')->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
