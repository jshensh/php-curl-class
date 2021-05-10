<?php
use CustomCurl\Client;

$curlSet1 = Client::init('http://127.0.0.1/examples/example_server.php')
    ->set('referer', 'http://google.com');

$curlSet2 = clone $curlSet1;

$curlObj1 = $curlSet1->setHeader('X-PJAX', 'true')->exec();
$curlObj2 = $curlSet2->setHeader('X-Requested-With', 'XMLHttpRequest')->exec();

if (!$curlObj1->getStatus()) {
    throw new \Exception('Curl Error', $curlObj1->getCurlErrNo());
}

var_dump($curlObj1->getHeader(), $curlObj1->getCookies(), $curlObj1->getBody(), $curlObj1->getInfo());

if (!$curlObj2->getStatus()) {
    throw new \Exception('Curl Error', $curlObj2->getCurlErrNo());
}

var_dump($curlObj2->getHeader(), $curlObj2->getCookies(), $curlObj2->getBody(), $curlObj2->getInfo());
