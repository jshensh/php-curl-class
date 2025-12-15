<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class ApiListController extends Controller
{
    private function autoAddId($list)
    {
        foreach ($list as $k => &$item) {
            $item['id'] = $item['name'];
        }
        return $list;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $domain = '/api';

        $list = [
            [
                'name'    => '/test - GET - GET 请求示例',
                'url'     => "{$domain}/test",
                'method'  => 'GET',
                'params'  => [
                    'page' => 1
                ]
            ],
            [
                'name'    => '/test - POST - POST 表单示例',
                'url'     => "{$domain}/test",
                'method'  => 'POST',
                'params'  => [
                    'file' => ['type' => 'file'],
                    'text' => [
                        'type'        => 'text',
                        'value'       => 'text',
                        'description' => '接口参数注释示例'
                    ],
                ]
            ],
            [
                'name'    => '/test - POST - POST JSON 示例',
                'url'     => "{$domain}/test",
                'method'  => 'POST',
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'params'  => [
                    'name'  => '测试',
                    'array' => [
                        'key' => 'value'
                    ],
                ]
            ],
            [
                'name'    => '/test - POST - POST 二进制数据示例',
                'url'     => "{$domain}/test",
                'method'  => 'POST',
                'headers' => [
                    'Content-Type' => 'application/octet-stream'
                ],
                'params'  => '31 32 33'
            ],
            [
                'name'    => '/test/file - GET - 下载文件示例',
                'url'     => "{$domain}/test/file",
                'method'  => 'GET',
                'params'  => [],
                'config'  => [
                    'isDownloadFile' => true
                ]
            ]
        ];

        usort($list, function($a, $b) {
            $apiMethodList = ['GET', 'POST', 'PUT', 'DELETE'];
            $sortWeight = [0, 0];
            $index = [0, 0];

            foreach ([$a, $b] as $key => $value) {
                foreach ($apiMethodList as $sort => $method) {
                    $tmpIndex = strpos($value['name'], " - {$method} - ");
                    if ($tmpIndex !== false) {
                        $sortWeight[$key] = $sort;
                        $index[$key] = $tmpIndex;
                        break;
                    }
                }
            }

            if ($a['name'] === $b['name']) {
                return 0;
            }

            $a['name'] = $index[0] ? substr($a['name'], 0, $index[0]) : $a['name'];
            $b['name'] = $index[1] ? substr($b['name'], 0, $index[1]) : $b['name'];

            return (
                $a['name'] < $b['name'] ||
                (
                    $a['name'] === $b['name'] &&
                    $sortWeight[0] < $sortWeight[1]
                )
            ) ? -1 : 1;
        });

        if ($search) {
            foreach ($list as $k => $item) {
                if (strpos($item['name'], $search) === false) {
                    unset($list[$k]);
                }
            }
        }

        return response()->json($this->autoAddId(array_values($list)));
    }
}
