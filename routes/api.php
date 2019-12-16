<?php
use Illuminate\Routing\Router;

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
        ['get', 'cms/page/categories/tree'], ['get', 'cms/page/articles'],
        
        ['get', 'wechat/mp/auth'], ['post', 'wechat/mp/qrcode'],

        ['post', 'user/login/quick', 'token'], ['post', 'user/register/quick', 'token'], ['get', 'user/me'],
    ];

    foreach ($registry as $item) {
        $method = $item[0];
        $url = $item[1];
        $function = $item[2] ?? 'api';
        
        $router->{$method}($url, 'ServiceProxyController@' . $function);
    }
});