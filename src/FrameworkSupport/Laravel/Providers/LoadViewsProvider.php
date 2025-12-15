<?php

namespace CustomCurl\FrameworkSupport\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class LoadViewsProvider extends ServiceProvider
{
    /**
     * 在注册后启动服务。
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'CustomCurl');

        View::composer('CustomCurl::ApiDebugger', function ($view) {
            $defaultOptions = [
                'loginUrl'  => '',
                'loginForm' => [
                    [
                        'key'         => 'email',
                        'label'       => 'Email',
                        'placeholder' => 'Email',
                        'value'       => 'admin@example.com',
                        'type'        => 'text'
                    ],
                    [
                        'key'         => 'password',
                        'label'       => 'Password',
                        'placeholder' => 'Password',
                        'value'       => 'password',
                        'type'        => 'password'
                    ],
                ],
                'apiListUrl' => '/apilist',
                'loginToken' => "data['access_token']"
            ];

            foreach ($defaultOptions as $key => $value) {
                if (!$view->__isset($key)) {
                    $view->with($key, $value);
                }
            }
        });

        if (function_exists('app_path')) {
            $this->publishes([
                __DIR__.'/../Http/Controller/ApiListController.php' => app_path('Http/Controllers/ApiListController.php')
            ], 'ApiDebugger');
        }
    }
}