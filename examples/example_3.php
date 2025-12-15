<?php
use CustomCurl\Client;

$curlObj = Client::init('http://127.0.0.1/examples/example_server.php', 'post')
    ->set('postFields', [
        'fname'    => 'jshensh',
        'files[0]' => new CURLFile('./README.md'),
        'files[1]' => new CURLFile('LICENSE')
    ])
    ->set('postFieldsBuildQuery', false)    // postFieldsBuildQuery 设置为 True 时，将对 postFields 进行 http_build_query，避免出现跨语言无法 POST 数据的问题。如需上传文件则需要将该项设置为 False。
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());