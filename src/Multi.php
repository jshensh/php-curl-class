<?php
// +----------------------------------------------------------------------
// | Custom Curl
// +----------------------------------------------------------------------
// | Copyright (c) 2021 http://233.imjs.work All rights reserved.
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
            $chDataArr = [],
            $chArrEndFlag = false;

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
    }

    /**
     * 获取 $clientArr 中的 Curl 句柄
     * @access private
     * @param int|string $clientIndex 索引
     * @return resource
     */
    private function getCh($clientIndex = null)
    {
        if ($clientIndex === null) {
            if ($this->chArrEndFlag) {
                return false;
            }
            
            $clientIndex = key($this->clientArr);
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

        if (next($this->clientArr) === false) {
            $this->chArrEndFlag = true;
        }
        return $this->chArr[$clientIndex];
    }

    /**
     * 执行多线程请求
     * @access public
     * @return array
     */
    public function exec()
    {
        $mh = curl_multi_init();
        $active = null;

        for ($i = 0; $i < ($this->multiOptions['concurrency'] !== null ? $this->multiOptions['concurrency'] : count($this->clientArr)); $i++) {
            $ch = $this->getCh();
            if (!$ch) {
                break;
            }
            curl_multi_add_handle($mh, $ch);
        }

        $reRequestPool = [];

        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                    $info = curl_multi_info_read($mh);
                    if ($info !== false) {
                        $index = array_search($info['handle'], $this->chArr, true);
                        if ($index !== false) {
                            $this->chDataArr[$index]['reRequest']--;
                            if ((int) $info['result'] && $this->chDataArr[$index]['reRequest'] > 0) {
                                $reRequestPool[] = $index;
                            }
                            $this->chDataArr[$index]['code'] = (int) $info['result'];
                        }
                    }
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }

            if ($reRequestPool || ($this->multiOptions['concurrency'] !== null && $active < $this->multiOptions['concurrency'])) {
                $nextCh = $this->getCh();
                $nextCh = $nextCh ? $nextCh : $this->getCh(array_shift($reRequestPool));
                if ($nextCh) {
                    curl_multi_add_handle($mh, $nextCh);
                    $mrc = curl_multi_exec($mh, $active);
                }
            }
        }

        foreach ($this->chArr as $i => $ch) {
            $output = curl_multi_getcontent($ch);
            $curlErrNo = $this->chDataArr[$i]['code'];
            if ($curlErrNo === 0 && $output) {
                $result[$i] = new Statement(0, $ch, $output, $this->chDataArr[$i]['cookieJar']);
            } else {
                $result[$i] = new Statement($curlErrNo, $ch, $output);
            }
        }

        return $result;
    }
}