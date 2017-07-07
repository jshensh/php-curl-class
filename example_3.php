<?php
require('customCurl.php');

$curlObj = CustomCurl::init('http://lab.imjs.work/server.php', 'put')
            ->set('postFields', ['fname' => 'jshensh'])
            ->set('postType', 'json')
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}