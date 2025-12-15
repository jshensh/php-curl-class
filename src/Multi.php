<?php
// +----------------------------------------------------------------------
// | Custom Curl
// +----------------------------------------------------------------------
// | Copyright (c) 2024 http://233.imjs.work All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://www.gnu.org/licenses/gpl-3.0.html )
// +----------------------------------------------------------------------
// | Author: jshensh <admin@imjs.work>
// +----------------------------------------------------------------------

namespace CustomCurl;

use CustomCurl\Client;

/**
 * Custom Curl 多线程类
 * @author  jshensh <admin@imjs.work>
 */
class Multi
{
    // $chDataArr = [['cookieJar' => $cookieJar, 'code' => $code, 'reRequest' => $reRequest]];
    private $multiOptions = [
                'concurrency' => null
            ],
            $clientArr = [],
            $chArr = [],
            $chGenerator = null,
            $chDataArr = [];

    /**
     * 获取 $this->chArr 的生成器
     * @access private
     * @return \Generator
     */
    private function getChGenerator()
    {
        // make function getChGenerator compatible with PHP 5.6
        foreach ($this->clientArr as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * 构造方法
     * @access public
     * @param array $clientArr CustomCurl\Client 的集合数组
     * @param array $options 选项数组
     * @return void
     */
    public function __construct($clientArr, $options = [])
    {
        foreach ($clientArr as $client) {
            if (!($client instanceof Client)) {
                throw new \Exception('Argument 1 must be a collection of instances of CustomCurl\\Client');
            }
        }

        $this->multiOptions = array_merge($this->multiOptions, $options);
        $this->clientArr = $clientArr;
        $this->chGenerator = $this->getChGenerator();
    }

    /**
     * 获取 $clientArr 中的 Curl 句柄
     * @access private
     * @param int|string $clientIndex 索引
     * @return array
     */
    private function getCh($clientIndex = null)
    {
        if ($clientIndex === null) {
            if (!$this->chGenerator->valid()) {
                return false;
            }

            $clientIndex = $this->chGenerator->key();
            $this->chGenerator->next();
        }

        if (!isset($this->clientArr[$clientIndex])) {
            return false;
        }

        $client = $this->clientArr[$clientIndex];
        $this->chArr[$clientIndex] = $client->getHandle();
        $cookieJar = &$client->getCookieJar();

        if (!isset($this->chDataArr[$clientIndex])) {
            $this->chDataArr[$clientIndex] = [
                'cookieJar' => $cookieJar,
                'code'      => null,
                'reRequest' => $client->get('reRequest')
            ];
        }

        return [$clientIndex, $this->chArr[$clientIndex]];
    }

    /**
     * 执行多线程请求
     * @access public
     * @return \Generator
     */
    public function cursor()
    {
        $mh = curl_multi_init();
        $active = null;

        $concurrency = isset($this->multiOptions['concurrency'])
            ? $this->multiOptions['concurrency']
            : count($this->clientArr);

        $runningHandles = []; // key = (int)$handle, value = 用户 index
        $handleMap = [];      // key = (int)$handle, value = 用户 index

        // 初始化并发 handle
        for ($i = 0; $i < $concurrency; $i++) {
            $ch = $this->getCh();
            if (!$ch) {
                break;
            }

            // getCh() 返回 handle，对应 index 就是 handle 在 $chArr 的 key
            $index = $ch[0];
            if ($index === false) {
                continue;
            }

            curl_multi_add_handle($mh, $ch[1]);
            $chInt = (int)$ch[1];
            $runningHandles[$chInt] = $index;
            $handleMap[$chInt] = $index;
        }

        do {
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            if ($active) {
                curl_multi_select($mh);
            }

            // 处理完成的 handle
            while ($info = curl_multi_info_read($mh)) {
                $chInt = (int)$info['handle'];

                if (!isset($handleMap[$chInt])) {
                    curl_multi_remove_handle($mh, $info['handle']);
                    curl_close($info['handle']);
                    unset($runningHandles[$chInt]);
                    continue;
                }

                $index = $handleMap[$chInt];
                unset($handleMap[$chInt], $runningHandles[$chInt]);

                $output = curl_multi_getcontent($info['handle']);
                curl_multi_remove_handle($mh, $info['handle']);

                // 更新重试次数
                $this->chDataArr[$index]['reRequest']--;

                // 判断是否需要重试
                if ((int)$info['result'] !== 0 || !$output) {
                    if ($this->chDataArr[$index]['reRequest'] > 0) {
                        curl_close($info['handle']);
                        $retryCh = $this->getCh($index);
                        if ($retryCh) {
                            curl_multi_add_handle($mh, $retryCh[1]);
                            $retryChInt = (int)$retryCh[1];
                            $runningHandles[$retryChInt] = $index;
                            $handleMap[$retryChInt] = $index;
                        }
                        continue; // 不 yield
                    }
                }

                // 成功或耗尽重试才 yield
                yield $index => new Statement(
                    (int)$info['result'],
                    $info['handle'],
                    $output,
                    $this->chDataArr[$index]['cookieJar']
                );

                // 释放 handle
                curl_close($info['handle']);
            }

            // 补充 handle 保持并发
            while (count($runningHandles) < $concurrency && $this->chGenerator->valid()) {
                $ch = $this->getCh();
                if (!$ch) {
                    break;
                }

                $index = $ch[0];

                curl_multi_add_handle($mh, $ch[1]);
                $chInt = (int)$ch[1];
                $runningHandles[$chInt] = $index;
                $handleMap[$chInt] = $index;
            }

        } while (!empty($runningHandles) || $this->chGenerator->valid());

        curl_multi_close($mh);
    }

}