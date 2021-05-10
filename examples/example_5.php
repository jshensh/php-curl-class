<?php
use CustomCurl\Client;

$cookieJar = [];

$curlObj = Client::init('http://127.0.0.1/examples/example_server.php')
    ->cookieJar($cookieJar)   // 设置 CookieJar，类似于 CURLOPT_COOKIEJAR，可在多次交互过程中自动存取 Cookies
    ->setCookie('a', 'b')     // 设置 Cookie，Key => Value
    ->clearCookies()          // 清空之前设置的所有 Cookie
    ->setCookie('b', 'c')     // 重新设置 Cookie，Key => Value
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
