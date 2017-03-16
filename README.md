Custom Curl (嗯懒得取名字)
==========================

对 PHP 自带 curl 的一个简单的封装，支持链式操作。

## 目录

- [使用示例](#使用示例)
    - [GET 方法](#get-方法)
    - [POST 方法](#post-方法)
    - [Cookie](#cookie)
    - [Header](#header)
- [设置项](#设置项)
- [杂项](#杂项)


## 使用示例

### GET 方法

```php
$curlSet = CustomCurl::init('http://cn.bing.com/search?q=php');

$curlObj = $curlSet->exec();
if ($curlObj) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody());
} else {
    var_dump($curlSet->getCurlErrNo());
}
```

### POST 方法

```php
$curlSet = CustomCurl::init('http://www.w3school.com.cn/example/php/demo_php_global_post.php', 'post')
            ->set('postFields', ['fname' => 'jshensh']);

$curlObj = $curlSet->exec();
if ($curlObj) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody());
} else {
    var_dump($curlSet->getCurlErrNo());
}
```

### Cookie

```php
$curlSet = CustomCurl::init('http://example.com')
            ->setCookie('a', 'b')     // 设置 Cookie，Key => Value
            ->clearCookie()           // 清空之前设置的所有 Cookie
            ->setCookie('a', 'b');    // 重新设置 Cookie，Key => Value

$curlObj = $curlSet->exec();
if ($curlObj) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody());
} else {
    var_dump($curlSet->getCurlErrNo());
}
```

### Header

```php
$curlSet = CustomCurl::init('http://example.com/api')
            ->setHeader('X-PJAX', 'true')                         // 设置 Header，Key => Value
            ->clearCookie()                                       // 清空之前设置的所有 Header
            ->setHeader('X-Requested-With', 'XMLHttpRequest');    // 设置 Header，Key => Value

$curlObj = $curlSet->exec();
if ($curlObj) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody());
} else {
    var_dump($curlSet->getCurlErrNo());
}
```

## 设置项

```php
$curlSet = CustomCurl::init('http://cn.bing.com')
            ->set('referer', 'http://google.com')           // 设置 HTTP REFERER
            ->set('ignoreCurlError', 1)                     // 忽略 Curl 错误，默认值 False
            ->set('timeout', 1)                             // CURLOPT_TIMEOUT，单位秒，默认值 5
            ->set('reRequest', 1)                           // 遇到错误时重新尝试的次数，默认值 3
            ->set('postFields', ['fname' => 'jshensh'])     // POST 提交参数，数组
            ->set('followLocation', 1)                      // CURLOPT_FOLLOWLOCATION，默认值 True
            ->set('autoRefer', 1)                           // CURLOPT_AUTOREFERER，默认值 True
            ->set('maxRedirs', 1)                           // CURLOPT_MAXREDIRS，默认值 3
            ->set('userAgent', 'Mozilla');                  // CURLOPT_USERAGENT

$curlObj = $curlSet->exec();
if ($curlObj) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody());
} else {
    var_dump($curlSet->getCurlErrNo());
}
```

## 杂项

### 多次请求同一地址

```php
$curlSet1 = CustomCurl::init('http://cn.bing.com')
                ->set('referer', 'http://google.com');

$curlSet2 = clone $curlSet2;

$curlSet1 = $curlSet1->setHeader('X-PJAX', 'true');
$curlSet2 = $curlSet2->setHeader('X-Requested-With', 'XMLHttpRequest');

$curlObj1 = $curlSet1->exec();
if ($curlObj1) {
    var_dump($curlObj1->getHeader(), $curlObj1->getCookies(), $curlObj1->getBody());
} else {
    var_dump($curlSet1->getCurlErrNo());
}

$curlObj2 = $curlSet2->exec();
if ($curlObj2) {
    var_dump($curlObj2->getHeader(), $curlObj2->getCookies(), $curlObj2->getBody());
} else {
    var_dump($curlSet2->getCurlErrNo());
}
```