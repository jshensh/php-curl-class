<?php
use CustomCurl\Client;

$curlObj = Client::init('http://127.0.0.1/examples/example_server.php', 'put')
    ->set('postFields', ['fname' => 'jshensh'])
    ->set('postType', 'json')
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());