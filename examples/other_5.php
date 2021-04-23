<?php
use CustomCurl\Client;

$cookieJar = [];

$multiCurl = Client::multi([
    'test6' => Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
    'test4' => Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
    'test3' => Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
    'test1' => Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
    'test9' => Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
    'test8' => Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
], ['concurrency' => 3]);

foreach ($multiCurl as $curlObj) {
    var_dump($curlObj->getStatus(), $curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
}

var_dump($cookieJar);

$multiCurl = Client::multi([
    Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
    Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
]);

foreach ($multiCurl as $curlObj) {
    var_dump($curlObj->getStatus(), $curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
}

var_dump($cookieJar);