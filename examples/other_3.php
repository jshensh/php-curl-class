<?php
use CustomCurl\Client;

Client::setConf('timeout', 3);
Client::setConf('reRequest', 1);
Client::setConf('maxRedirs', 1);
Client::setConf('ignoreCurlError', 1);
Client::setConf('followLocation', 1);
Client::setConf('referer', 'http://google.com');
Client::setConf('userAgent', 'Mozilla');
Client::setConf('customHeader', ['X-PJAX: true']);
Client::setConf('sendCookies', 'a=b; b=c');
Client::setConf('autoRefer', 1);
Client::setConf('postType', 'json');
Client::setConf('proxy', '');
Client::setConf('proxyPort', 8080);
Client::setConf('proxyUserPwd', '');
Client::setConf('proxyType', '');
Client::setConf('postFieldsBuildQuery', false);
Client::setConf('postFieldsMultiPart', true);

$curlObj0 = Client::init('http://127.0.0.1/examples/example_server.php')
    ->set('userAgent', 'Test')
    ->exec();

if (!$curlObj0->getStatus()) {
    throw new \Exception('Curl Error', $curlObj0->getCurlErrNo());
}

var_dump($curlObj0->getHeader(), $curlObj0->getCookies(), $curlObj0->getBody(), $curlObj0->getInfo());

$curlObj1 = Client::init('http://127.0.0.1/examples/example_server.php')->exec();

if (!$curlObj1->getStatus()) {
    throw new \Exception('Curl Error', $curlObj1->getCurlErrNo());
}

var_dump($curlObj1->getHeader(), $curlObj1->getCookies(), $curlObj1->getBody(), $curlObj1->getInfo());

Client::resetConf('userAgent');

$curlObj2 = Client::init('http://127.0.0.1/examples/example_server.php')->exec();

if (!$curlObj2->getStatus()) {
    throw new \Exception('Curl Error', $curlObj2->getCurlErrNo());
}

var_dump($curlObj2->getHeader(), $curlObj2->getCookies(), $curlObj2->getBody(), $curlObj2->getInfo());

Client::resetConf();

$curlObj3 = Client::init('http://127.0.0.1/examples/example_server.php')->exec();

if (!$curlObj3->getStatus()) {
    throw new \Exception('Curl Error', $curlObj3->getCurlErrNo());
}

var_dump($curlObj3->getHeader(), $curlObj3->getCookies(), $curlObj3->getBody(), $curlObj3->getInfo());