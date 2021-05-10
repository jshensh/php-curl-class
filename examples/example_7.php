<?php
use CustomCurl\Client;

$curlObj = Client::init('http://127.0.0.1/examples/example_server.php')
    ->setHeader('X-PJAX', 'true')                         // 设置 Header，Key => Value
    ->clearHeaders()                                      // 清空之前设置的所有 Header
    ->setHeader('X-Requested-With', 'XMLHttpRequest')     // 设置 Header，Key => Value
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
