<?php
use Illuminate\Routing\Router;

/*
 * 微服务接口
 */
Route::group(['prefix' => 'svc', 'namespace' => 'ZhuiTech\BootLaravel\Controllers'], function (Router $router) {
    $registry = [
        ['get', 'logistics/regions/select'], ['get', 'logistics/regions'], ['get', 'logistics/companies'],
        ['post', 'sms/verify'], ['post', 'sms/check'],
        ['get', 'cms/ads/slug/{slug}'],
        ['post', 'pay/notify/{channel}'], ['post', 'pay/return/{channel}/{method}'], ['post', 'pay/notify/{channel}/{method}'],
    ];

    foreach ($registry as $item) {
        $method = $item[0];
        $router->{$method}($item[1], 'ServiceProxyController@api');
    }
});