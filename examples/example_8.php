<?php
require('../CustomCurl.php');

$curlObj = CustomCurl::init('http://example.com')
            ->setCurlOpt(CURLOPT_SSL_VERIFYPEER, true)  // CURLOPT_SSL_VERIFYPEER，默认值 False
            ->setCurlOpt(CURLOPT_SSL_VERIFYHOST, true)  // CURLOPT_SSL_VERIFYHOST，默认值 False
            ->setCurlOpt(CURLOPT_ENCODING, '')          // CURLOPT_ENCODING，默认值 ''
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}