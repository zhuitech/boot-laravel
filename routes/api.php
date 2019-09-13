<?php

use Illuminate\Routing\Router;

/*
 * 微服务接口
 */
Route::group(['prefix' => 'svc', 'namespace' => 'ZhuiTech\BootLaravel\Controllers'], function (Router $router) {
    $registry = [
        ['get', 'logistics/regions/select'], ['get', 'logistics/regions']
    ];

    foreach ($registry as $item) {
        $method = $item[0];
        $router->{$method}($item[1], 'ServiceProxyController@api');
    }
});