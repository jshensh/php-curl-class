Custom Curl (嗯懒得取名字)
==========================

对 PHP 自带 curl 的一个简单的封装，支持链式操作。

## 使用示例

### GET 方法

```php
use CustomCurl\Client;

$curlObj = Client::init('http://cn.bing.com/search?q=php')->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
```

### POST 方法

```php
use CustomCurl\Client;

$curlObj = Client::init('http://www.w3school.com.cn/example/php/demo_php_global_post.php', 'post')
    ->set('postFields', ['fname' => 'jshensh'])
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
```

### POST 上传文件

```php
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
```

### PUT 方法，传输 JSON 数据

```php
use CustomCurl\Client;

$curlObj = Client::init('http://lab.imjs.work/server.php', 'put')
    ->set('postFields', ['fname' => 'jshensh']) // 可以传入数组
    ->set('postFields', '{"fname": "jshensh"}') // 也可以直接传入 json 字符串
    ->set('postType', 'json')
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
```

### 手动设置 Cookie

```php
use CustomCurl\Client;

$cookieJar = [];
$curlObj = Client::init('http://example.com')
    ->cookieJar($cookieJar)   // 设置 CookieJar，类似于 CURLOPT_COOKIEJAR，可在多次交互过程中自动存取 Cookies
    ->setCookie('a', 'b')     // 设置 Cookie，Key => Value
    ->clearCookies()          // 清空之前设置的所有 Cookie
    ->setCookie('b', 'c')     // 重新设置 Cookie，Key => Value
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
```

### Cookie 字符串/数组

```php
use CustomCurl\Client;

$curlObj = Client::init('http://example.com')
    ->setCookie('a', 'b')             // 设置 Cookie，Key => Value
    ->setCookies('b=c; c=d')          // 传入字符串设置 Cookie，之前设置的 cookie 失效
    ->setCookies('d=e; c=f', true)    // 传入字符串追加设置 Cookie，Key 相同的 cookie 将会被覆盖
    ->setCookies(['e' => 'f'], true)  // 传入数组追加设置 Cookie，只允许传入一维数组
    ->setCookies(['e' => ['f', 'g']]) // 传入二维数组将忽略
    ->setCookies('dsjdhs')            // 传入不合法字符串将忽略
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
```

### Header

```php
use CustomCurl\Client;

$curlObj = Client::init('http://example.com/api')
    ->setHeader('X-PJAX', 'true')                         // 设置 Header，Key => Value
    ->clearHeaders()                                      // 清空之前设置的所有 Header
    ->setHeader('X-Requested-With', 'XMLHttpRequest')     // 设置 Header，Key => Value
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
```

### 自定义 CurlOpt

```php
use CustomCurl\Client;

$headerSize = -1;

$curlObj = Client::init('http://example.com')
    ->setCurlOpt(CURLOPT_SSL_VERIFYPEER, false)                                   // CURLOPT_SSL_VERIFYPEER，默认值 True
    ->setCurlOpt(CURLOPT_SSL_VERIFYHOST, 0)                                       // CURLOPT_SSL_VERIFYHOST，默认值 2
    ->setCurlOpt(CURLOPT_NOBODY, false)                                           // CURLOPT_NOBODY，默认值 False
    ->setCurlOpt(CURLOPT_HEADER, true)                                            // CURLOPT_HEADER，默认值 True
    ->setCurlOpt(CURLOPT_ENCODING, '')                                            // CURLOPT_ENCODING，默认值 ''
    ->setCurlOpt(CURLOPT_SSLCERT, dirname(__FILE__) . '/client.crt')              // CURLOPT_SSLCERT，SSL 双向认证证书路径
    ->setCurlOpt(CURLOPT_SSLKEYPASSWD, '')                                        // CURLOPT_SSLKEYPASSWD，证书需要的密码
    ->setCurlOpt(CURLOPT_SSLCERTTYPE, 'PEM')                                      // CURLOPT_SSLCERTTYPE，证书的类型，支持的格式有 "PEM" (默认值), "DER" 和 "ENG"
    ->setCurlOpt(CURLOPT_SSLKEY, dirname(__FILE__) . '/client.key')               // CURLOPT_SSLKEY，SSL 双向认证证书的私钥
    ->setCurlOpt(CURLOPT_SSLKEYPASSWD, '')                                        // CURLOPT_SSLKEYPASSWD，CURLOPT_SSLKEY 私钥的密码
    ->setCurlOpt(CURLOPT_SSLKEYTYPE, 'PEM')                                       // CURLOPT_SSLKEYTYPE，私钥的加密类型，支持的密钥类型为 "PEM"(默认值)、"DER" 和 "ENG"
    ->setCurlOpt(CURLOPT_WRITEFUNCTION, function($ch, $data) use (&$headerSize) { // CURLOPT_WRITEFUNCTION，下载时的回调函数，通常用于反向代理即时传输数据
        $info = curl_getinfo($ch);
        if ($headerSize < $info['header_size']) {
            $headerSize = $info['header_size'];
            if ($data === "\r\n") {
                $headerSize += 2;
            }
            header($data, false);
        } else {
            echo $data;
        }
        ob_flush();
        flush();
        return strlen($data);
    })
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
```

### 并发请求

```php
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

foreach ($multiCurl as $k => $curlObj) {
    var_dump($k, $curlObj->getStatus(), $curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
}

var_dump($multiCurl->getReturn());

var_dump($cookieJar);

$multiCurl = Client::multi([
    Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
    Client::init('http://127.0.0.1/examples/example_server.php')->cookieJar($cookieJar),
]);

foreach ($multiCurl as $k => $curlObj) {
    var_dump($k, $curlObj->getStatus(), $curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
}

var_dump($multiCurl->getReturn());

var_dump($cookieJar);
```

## 设置项

```php
use CustomCurl\Client;

$curlObj = Client::init('http://cn.bing.com')
    ->set('referer', 'http://google.com')       // 设置 HTTP REFERER
    ->set('ignoreCurlError', 1)                 // 忽略 Curl 错误，默认值 False
    ->set('timeout', 1)                         // CURLOPT_TIMEOUT，单位秒，默认值 5
    ->set('reRequest', 1)                       // 遇到错误时重新尝试的次数，默认值 3
    ->set('postFields', ['fname' => 'jshensh']) // POST 提交参数，数组
    ->set('postType', 'json')                   // 提交方式，可选 ['form', 'json', 'string']，默认值 'form'
    ->set('followLocation', 1)                  // CURLOPT_FOLLOWLOCATION，默认值 False
    ->set('autoRefer', 1)                       // CURLOPT_AUTOREFERER，默认值 True
    ->set('maxRedirs', 1)                       // CURLOPT_MAXREDIRS，默认值 3
    ->set('userAgent', 'Mozilla')               // CURLOPT_USERAGENT
    ->set('postFieldsBuildQuery', false)        // POST 时是否 build 成字符串传递，默认值 true
    ->set('postFieldsMultiPart', true)          // POST 时是否以 multipart/form-data 传递，优先级高于 postFieldsBuildQuery，默认值 false
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
```

## 杂项

### 多次请求同一地址

```php
use CustomCurl\Client;

$curlObj1 = Client::init('http://cn.bing.com')
                ->set('referer', 'http://google.com');

$curlObj2 = clone $curlObj1;

$curlObj1 = $curlObj1->setHeader('X-PJAX', 'true')->exec();
$curlObj2 = $curlObj2->setHeader('X-Requested-With', 'XMLHttpRequest')->exec();

if (!$curlObj1->getStatus()) {
    throw new \Exception('Curl Error', $curlObj1->getCurlErrNo());
}

var_dump($curlObj1->getHeader(), $curlObj1->getCookies(), $curlObj1->getBody(), $curlObj1->getInfo());

if (!$curlObj2->getStatus()) {
    throw new \Exception('Curl Error', $curlObj2->getCurlErrNo());
}

var_dump($curlObj2->getHeader(), $curlObj2->getCookies(), $curlObj2->getBody(), $curlObj2->getInfo());
```

### 代理

```php
use CustomCurl\Client;

$curlObj = Client::init('http://example.com')
    ->set('proxy', '127.0.0.1')                    // 代理地址
    ->set('proxyPort', 8080)                       // 代理端口，默认 8080
    ->set('proxyUserPwd', '[username]:[password]') // 代理用户名密码，默认不设置
    ->set('proxyType', CURLPROXY_HTTP)             // 代理类型，可选 [CURLPROXY_HTTP, CURLPROXY_SOCKS4, CURLPROXY_SOCKS5, CURLPROXY_SOCKS4A, CURLPROXY_SOCKS5_HOSTNAME]，默认 CURLPROXY_HTTP，传入常量，不要加引号
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());
```

### 设置全局配置

```php
use CustomCurl\Client;

Client::setConf('timeout', 3);                            // CURLOPT_TIMEOUT，单位秒，默认值 5
Client::setConf('reRequest', 1);                          // 遇到错误时重新尝试的次数，默认值 3
Client::setConf('maxRedirs', 1);                          // CURLOPT_MAXREDIRS，默认值 3
Client::setConf('ignoreCurlError', 1);                    // 忽略 Curl 错误，默认值 False
Client::setConf('followLocation', 1);                     // CURLOPT_FOLLOWLOCATION，默认值 True
Client::setConf('referer', 'http://google.com');          // 设置 HTTP REFERER
Client::setConf('userAgent', 'Mozilla');                  // 设置 User-Agent
Client::setConf('customHeader', ['X-PJAX: true']);        // 设置 Header，要求传入数组
Client::setConf('sendCookies', 'a=b; b=c');               // 设置 Cookies，要求传入一维数组或者字符串
Client::setConf('autoRefer', 1);                          // CURLOPT_AUTOREFERER，默认值 True
Client::setConf('postType', 'json');                      // 提交方式，可选 ['form', 'json', 'string']，默认值 'form'
Client::setConf('proxy', '127.0.0.1');                    // 代理
Client::setConf('proxyPort', 8080);                       // 代理端口
Client::setConf('proxyUserPwd', '[username]:[password]'); // 代理用户名密码
Client::setConf('proxyType', CURLPROXY_HTTP);             // 代理方式
Client::setConf('postFieldsBuildQuery', false);           // POST 时是否 build 成字符串传递，默认值 true
Client::setConf('postFieldsMultiPart', true);             // POST 时是否以 multipart/form-data 传递，优先级高于 postFieldsBuildQuery，默认值 false
// 以上为所有可修改的全局配置项

$curlObj = Client::init('http://lab.imjs.work/server.php')
    ->set('userAgent', 'Test') // 在当前会话中覆盖预设值
    ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());

$curlObj1 = Client::init('http://lab.imjs.work/server.php')->exec();

if (!$curlObj1->getStatus()) {
    throw new \Exception('Curl Error', $curlObj1->getCurlErrNo());
}

var_dump($curlObj1->getHeader(), $curlObj1->getCookies(), $curlObj1->getBody(), $curlObj1->getInfo());

Client::resetConf('userAgent'); // 恢复 userAgent 参数为默认值

$curlObj2 = Client::init('http://lab.imjs.work/server.php')->exec();

if (!$curlObj2->getStatus()) {
    throw new \Exception('Curl Error', $curlObj2->getCurlErrNo());
}

var_dump($curlObj2->getHeader(), $curlObj2->getCookies(), $curlObj2->getBody(), $curlObj2->getInfo());

Client::resetConf(); // 恢复全部参数为默认值

$curlObj3 = Client::init('http://lab.imjs.work/server.php')->exec();

if (!$curlObj3->getStatus()) {
    throw new \Exception('Curl Error', $curlObj3->getCurlErrNo());
}

var_dump($curlObj3->getHeader(), $curlObj3->getCookies(), $curlObj3->getBody(), $curlObj3->getInfo());
```

### 设置全局 CurlOpt 配置项

```php
use CustomCurl\Client;

Client::setCurlOptConf(CURLOPT_SSL_VERIFYPEER, false);  // CURLOPT_SSL_VERIFYPEER，默认值 True
Client::setCurlOptConf(CURLOPT_SSL_VERIFYHOST, 0);      // CURLOPT_SSL_VERIFYHOST，默认值 2
Client::setCurlOptConf(CURLOPT_ENCODING, 'gzip');       // CURLOPT_ENCODING，默认值 ''
// 以上为所有可修改的全局 CurlOpt 配置项

$curlObj = Client::init('http://lab.imjs.work/server.php')
            ->setCurlOpt(CURLOPT_ENCODING, '') // 在当前会话中覆盖预设值
            ->exec();

if (!$curlObj->getStatus()) {
    throw new \Exception('Curl Error', $curlObj->getCurlErrNo());
}

var_dump($curlObj->getHeader(), $curlObj->getCookies(), $curlObj->getBody(), $curlObj->getInfo());

$curlObj1 = Client::init('http://lab.imjs.work/server.php')->exec();

if (!$curlObj1->getStatus()) {
    throw new \Exception('Curl Error', $curlObj1->getCurlErrNo());
}

var_dump($curlObj1->getHeader(), $curlObj1->getCookies(), $curlObj1->getBody(), $curlObj1->getInfo());

Client::resetCurlOptConf(CURLOPT_ENCODING); // 恢复 CURLOPT_ENCODING 为默认值

$curlObj2 = Client::init('http://lab.imjs.work/server.php')->exec();

if (!$curlObj2->getStatus()) {
    throw new \Exception('Curl Error', $curlObj2->getCurlErrNo());
}

var_dump($curlObj2->getHeader(), $curlObj2->getCookies(), $curlObj2->getBody(), $curlObj2->getInfo());

Client::resetCurlOptConf(); // 恢复全部 CurlOpt 配置为默认值

$curlObj3 = Client::init('http://lab.imjs.work/server.php')->exec();

if (!$curlObj3->getStatus()) {
    throw new \Exception('Curl Error', $curlObj3->getCurlErrNo());
}

var_dump($curlObj3->getHeader(), $curlObj3->getCookies(), $curlObj3->getBody(), $curlObj3->getInfo());
```

## Laravel / Lumen 支持

### ApiDebugger

ApiDebugger 是一个简单的类似 Postman 的单页接口调试工具。

CustomCurl 起初只是一个服务端的 Curl 封装，由于在随后的使用过程中发现，我们一般都用它来模拟请求其他业务的接口，甚至是[搭建反向代理](https://github.com/jshensh/phpReverseProxy)，于是就产生了对反向代理接口进行调试的需求，作为并不喜欢使用 Postman 的我就写了这个工具（后端狗用上世纪 jQuery 码的东西，不要笑）。

因为浏览器限制，在一般情况下 ApiDebugger 只能调试当前域名下的所有资源，但我们可以通过在被调试目标上添加 ``Access-Control-Allow-Origin`` 等 Header 或者使用反向代理解决问题。

#### 开始使用

一般情况下，只要在 ``routes/web.php`` 中加入以下代码即可使用

```php
if (env('APP_DEBUG')) {
    Route::get('/debugger', function () {
        return view('CustomCurl::ApiDebugger');
    });
}
```

我们可以向 view 传递以下数据

```php
return view('CustomCurl::ApiDebugger', [
    'loginUrl'  => '',                     // 登录接口的 url，接口需要以 json 格式返回 token
    'loginForm' => [                       // 登录表单
        [
            'key'         => 'username',
            'label'       => 'Username',
            'placeholder' => 'Username',
            'value'       => 'admin',
            'type'        => 'text'        // dom 的类型，可选值 [number, email, text, number, password, hidden, html]
        ],
        [
            'key'         => 'password',
            'label'       => 'Password',
            'placeholder' => 'Password',
            'value'       => 'password',
            'type'        => 'password'
        ],
        [
            'type' => 'html',
            'html' => '<p>test</p>'        // 当 type 为 html 时，需要传入 html 的内容
        ],
    ],
    'apiListUrl' => '/apilist',            // api 列表接口
    'loginToken' => "data['access_token']" // 登录接口返回的 token 下标
]);
```

#### 预置 Api 列表

向 ``app/Http/Controller`` 目录发布 ``ApiListController.php``

```php
php artisan vendor:publish --tag=ApiDebugger
```

在之前的基础上继续设置路由

```php
if (env('APP_DEBUG')) {
    Route::get('/debugger', function () {
        return view('CustomCurl::ApiDebugger');
    });
    Route::get('/apilist', [\App\Http\Controllers\ApiListController::class, 'index']);
}
```

由于 Lumen 不支持 ``vendor:publish`` 操作，请手动将 [ApiListController.php](https://github.com/jshensh/php-curl-class/blob/master/src/FrameworkSupport/Laravel/Http/Controller/ApiListController.php) 复制至对应目录

#### 注册 Provider

由于 Lumen 不具备 Laravel 的 Discovered Package 功能，所以我们在使用 Lumen 时，需要手动注册 Provider。

请向 ``/bootstrap/app.php`` 中 ``Register Service Providers`` 代码段中添加以下代码

```php
$app->register(CustomCurl\FrameworkSupport\Laravel\Providers\LoadViewsProvider::class);
```

## 版权信息

Custom Curl 遵循 GPL-3.0 开源协议发布，并提供免费使用。

版权所有 Copyright © 2018 - 2021 by jshensh (http://233.imjs.work)

All rights reserved。

更多细节参阅 [LICENSE](LICENSE)
