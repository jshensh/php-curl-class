<?php
require('customCurl.php');

$curlObj = CustomCurl::init('http://lab.imjs.work/server.php')
            ->setCookie('a', 'b')     // 设置 Cookie，Key => Value
            ->clearCookies()          // 清空之前设置的所有 Cookie
            ->setCookie('b', 'c')     // 重新设置 Cookie，Key => Value
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody());
} else {
    var_dump($curlObj->getCurlErrNo());
}
