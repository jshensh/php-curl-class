<?php
require('customCurl.php');

$curlObj = CustomCurl::init('http://cn.bing.com/search?q=php')->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody());
} else {
    var_dump($curlObj->getCurlErrNo());
}
