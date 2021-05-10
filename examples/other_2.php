<?php
use CustomCurl\Client;

$curlObj = Client::init('http://127.0.0.1/examples/example_server.php')
    ->set('proxy', '127.0.0.1')
    ->set('proxyPort', 8080)
    ->set('proxyUserPwd', '[username]:[password]')
    ->set('proxyType', CURLPROXY_HTTP)
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());