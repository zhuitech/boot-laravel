<?php
use Illuminate\Routing\Router;

/*
 * 微服务接口
 */
Route::group(['prefix' => 'svc', 'namespace' => 'ZhuiTech\BootLaravel\Controllers'], function (Router $router) {
    $registry = [
        ['get', 'logistics/regions/select'], ['get', 'logistics/regions'], ['get', 'logistics/companies'], ['get', 'logistics/query'],
        ['get', 'system/shipping/query'], ['get', 'system/shipping/company'],

        ['post', 'sms/verify'], ['post', 'sms/check'],

        ['post', 'pay/notify/{channel}'], ['post', 'pay/return/{channel}/{method}'], ['post', 'pay/notify/{channel}/{method}'],

        ['post', 'system/qrcode'], ['get', 'system/qrcode'],
        ['post', 'system/cache/{key}'], ['get', 'system/cache/{key}'],

        ['post', 'file/upload/{strategy}'], ['post', 'file/upload/callback/{disk}'], ['post', 'file/upload/{disk}'], ['post', 'file/upload/form/token'],

        ['get', 'cms/ads/slug/{slug}'],
        ['get', 'cms/page/categories/tree'], ['get', 'cms/page/articles']
    ];

    foreach ($registry as $item) {
        $method = $item[0];
        $router->{$method}($item[1], 'ServiceProxyController@api');
    }
});