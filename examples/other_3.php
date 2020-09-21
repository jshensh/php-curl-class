<?php
require('../CustomCurl.php');

CustomCurl::setConf('timeout', 3);
CustomCurl::setConf('reRequest', 1);
CustomCurl::setConf('maxRedirs', 1);
CustomCurl::setConf('ignoreCurlError', 1);
CustomCurl::setConf('followLocation', 1);
CustomCurl::setConf('referer', 'http://google.com');
CustomCurl::setConf('userAgent', 'Mozilla');
CustomCurl::setConf('customHeader', ['X-PJAX: true']);
CustomCurl::setConf('sendCookies', 'a=b; b=c');
CustomCurl::setConf('autoRefer', 1);
CustomCurl::setConf('postType', 'json');
CustomCurl::setConf('proxy', '');
CustomCurl::setConf('proxyPort', 8080);
CustomCurl::setConf('proxyUserPwd', '');
CustomCurl::setConf('proxyType', '');
CustomCurl::setConf('postFieldsBuildQuery', false);

$curlObj0 = CustomCurl::init('http://127.0.0.1/examples/example_server.php')
                ->set('userAgent', 'Test')
                ->exec();

if (!$curlObj0->getStatus()) {
    throw new \Exception('Curl Error', $curlObj0->getCurlErrNo());
}

var_dump($curlObj0->getHeader(), $curlObj0->getCookies(), $curlObj0->getBody(), $curlObj0->getInfo());

$curlObj1 = CustomCurl::init('http://127.0.0.1/examples/example_server.php')->exec();

if (!$curlObj1->getStatus()) {
    throw new \Exception('Curl Error', $curlObj1->getCurlErrNo());
}

var_dump($curlObj1->getHeader(), $curlObj1->getCookies(), $curlObj1->getBody(), $curlObj1->getInfo());

CustomCurl::resetConf('userAgent');

$curlObj2 = CustomCurl::init('http://127.0.0.1/examples/example_server.php')->exec();

if (!$curlObj2->getStatus()) {
    throw new \Exception('Curl Error', $curlObj2->getCurlErrNo());
}

var_dump($curlObj2->getHeader(), $curlObj2->getCookies(), $curlObj2->getBody(), $curlObj2->getInfo());

CustomCurl::resetConf();

$curlObj3 = CustomCurl::init('http://127.0.0.1/examples/example_server.php')->exec();

if (!$curlObj3->getStatus()) {
    throw new \Exception('Curl Error', $curlObj3->getCurlErrNo());
}

var_dump($curlObj3->getHeader(), $curlObj3->getCookies(), $curlObj3->getBody(), $curlObj3->getInfo());