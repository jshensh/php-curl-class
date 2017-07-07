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
$curlObj = CustomCurl::init('http://cn.bing.com/search?q=php')->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}
```

### POST 方法

```php
$curlObj = CustomCurl::init('http://www.w3school.com.cn/example/php/demo_php_global_post.php', 'post')
            ->set('postFields', ['fname' => 'jshensh'])
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}
```

### PUT 方法，传输 JSON 数据

```php
$curlObj = CustomCurl::init('http://lab.imjs.work/server.php', 'put')
            ->set('postFields', ['fname' => 'jshensh'])
            ->set('postType', 'json')
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}
```

### 手动设置 Cookie

```php
$curlObj = CustomCurl::init('http://example.com')
            ->setCookie('a', 'b')     // 设置 Cookie，Key => Value
            ->clearCookies()          // 清空之前设置的所有 Cookie
            ->setCookie('b', 'c')     // 重新设置 Cookie，Key => Value
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}
```

### Cookie 字符串/数组

```php
$curlObj = CustomCurl::init('http://example.com')
            ->setCookie('a', 'b')             // 设置 Cookie，Key => Value
            ->setCookies('b=c; c=d')          // 传入字符串设置 Cookie，之前设置的 cookie 失效
            ->setCookies('d=e; c=f', true)    // 传入字符串追加设置 Cookie，Key 相同的 cookie 将会被覆盖
            ->setCookies(['e' => 'f'], true)  // 传入数组追加设置 Cookie，只允许传入一维数组
            ->setCookies(['e' => ['f', 'g']]) // 传入二维数组将忽略
            ->setCookies('dsjdhs')            // 传入不合法字符串将忽略
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}
```

### Header

```php
$curlObj = CustomCurl::init('http://example.com/api')
            ->setHeader('X-PJAX', 'true')                         // 设置 Header，Key => Value
            ->clearHeaders()                                      // 清空之前设置的所有 Header
            ->setHeader('X-Requested-With', 'XMLHttpRequest')     // 设置 Header，Key => Value
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}
```

## 设置项

```php
$curlObj = CustomCurl::init('http://cn.bing.com')
            ->set('referer', 'http://google.com')           // 设置 HTTP REFERER
            ->set('ignoreCurlError', 1)                     // 忽略 Curl 错误，默认值 False
            ->set('timeout', 1)                             // CURLOPT_TIMEOUT，单位秒，默认值 5
            ->set('reRequest', 1)                           // 遇到错误时重新尝试的次数，默认值 3
            ->set('postFields', ['fname' => 'jshensh'])     // POST 提交参数，数组
            ->set('postType', 'json')                       // 提交方式，可选 ['form', 'json', 'string']
            ->set('followLocation', 1)                      // CURLOPT_FOLLOWLOCATION，默认值 True
            ->set('autoRefer', 1)                           // CURLOPT_AUTOREFERER，默认值 True
            ->set('maxRedirs', 1)                           // CURLOPT_MAXREDIRS，默认值 3
            ->set('userAgent', 'Mozilla')                   // CURLOPT_USERAGENT
            ->exec();

if ($curlObj->getStatus()) {
    var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj->getCurlErrNo());
}
```

## 杂项

### 多次请求同一地址

```php
$curlObj1 = CustomCurl::init('http://cn.bing.com')
                ->set('referer', 'http://google.com');

$curlObj2 = clone $curlObj1;

$curlObj1 = $curlObj1->setHeader('X-PJAX', 'true')->exec();
$curlObj2 = $curlObj2->setHeader('X-Requested-With', 'XMLHttpRequest')->exec();

if ($curlObj1->getStatus()) {
    var_dump($curlObj1->getHeader(), $curlObj1->getCookies(), $curlObj1->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj1->getCurlErrNo());
}

if ($curlObj2->getStatus()) {
    var_dump($curlObj2->getHeader(), $curlObj2->getCookies(), $curlObj2->getBody(), $curlObj->getInfo());
} else {
    var_dump($curlObj2->getCurlErrNo());
}
```

## 版权信息

Custom Curl 遵循 Apache2 开源协议发布，并提供免费使用。

版权所有Copyright © 2017 by jshensh (http://233.imjs.work)

All rights reserved。

更多细节参阅 [LICENSE](LICENSE)