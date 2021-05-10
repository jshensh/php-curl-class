<?php
use CustomCurl\Client;

$curlObj = Client::init('http://www.w3school.com.cn/example/php/demo_php_global_post.php', 'post')
    ->set('postFields', ['fname' => 'jshensh']) // 可以传入数组
    ->set('postFields', '{"fname": "jshensh"}') // 也可以直接传入 json 字符串
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
