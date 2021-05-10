<?php
use CustomCurl\Client;

$curlObj = Client::init('http://127.0.0.1/examples/example_server.php')
    ->setCookie('a', 'b')             // 设置 Cookie，Key => Value
    ->setCookies('b=c; c=d')          // 传入字符串设置 Cookie，之前设置的 cookie 失效
    ->setCookies('d=e; c=f', true)    // 传入字符串追加设置 Cookie，Key 相同的 cookie 将会被覆盖
    ->setCookies(['e' => 'f'], true)  // 传入数组追加设置 Cookie，只允许传入一维数组
    ->setCookies(['e' => ['f', 'g']]) // 传入二维数组将忽略
    ->setCookies('dsjdhs')            // 传入不合法字符串将忽略
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
