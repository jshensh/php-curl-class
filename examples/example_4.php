<?php
require('../customCurl.php');

$curlObj = CustomCurl::init('http://127.0.0.1/examples/example_server.php', 'put')
            ->set('postFields', ['fname' => 'jshensh'])
            ->set('postType', 'json')
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}