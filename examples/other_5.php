<?php
use CustomCurl\Client;

$cookieJar = [];

$multiCurl = Client::multi([
    Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
    Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
]);

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