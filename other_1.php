<?php
require('customCurl.php');

$curlSet1 = CustomCurl::init('http://lab.imjs.work/server.php')
                ->set('referer', 'http://google.com');

$curlSet2 = clone $curlSet1;

$curlObj1 = $curlSet1->setHeader('X-PJAX', 'true')->exec();
$curlObj2 = $curlSet2->setHeader('X-Requested-With', 'XMLHttpRequest')->exec();

if ($curlObj1->getStatus()) {
    var_dump($curlObj1->getHeader(), $curlObj1->getCookies(), $curlObj1->getBody());
} else {
    var_dump($curlObj1->getCurlErrNo());
}

if ($curlObj2->getStatus()) {
    var_dump($curlObj2->getHeader(), $curlObj2->getCookies(), $curlObj2->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj2->getCurlErrNo());
}
