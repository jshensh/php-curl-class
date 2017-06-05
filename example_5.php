<?php
require('customCurl.php');

$curlObj = CustomCurl::init('http://lab.imjs.work/server.php')
            ->setHeader('X-PJAX', 'true')                         // 设置 Header，Key => Value
            ->clearHeaders()                                      // 清空之前设置的所有 Header
            ->setHeader('X-Requested-With', 'XMLHttpRequest')     // 设置 Header，Key => Value
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}
