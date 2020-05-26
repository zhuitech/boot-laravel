<?php
use Illuminate\Routing\Router;

Route::group(['prefix' => 'svc', 'namespace' => 'ZhuiTech\BootLaravel\Controllers'], function (Router $router) {
    $registry = [
        ['get', 'logistics/regions/select'], ['get', 'logistics/regions'], ['get', 'logistics/companies'], ['get', 'logistics/query'],
        ['get', 'system/shipping/query'], ['get', 'system/shipping/company'],

        ['post', 'sms/verify'], ['post', 'sms/check'], ['post', 'sms/send'],

        ['post', 'pay/notify/{channel}'], ['post', 'pay/return/{channel}/{method}'], ['post', 'pay/notify/{channel}/{method}'],

        ['post', 'system/qrcode'], ['get', 'system/qrcode'],
        ['post', 'system/cache/{key}'], ['get', 'system/cache/{key}'],

        ['post', 'file/upload/{strategy}'], ['post', 'file/upload/callback/{disk}'], ['post', 'file/upload/{disk}'], ['post', 'file/upload/form/token'],

        ['get', 'cms/ads/slug/{slug}'],
        ['get', 'cms/page/categories/tree'], ['get', 'cms/page/articles'], ['get', 'cms/page/articles/{id}'], ['get', 'cms/page/types'],

        ['get', 'wechat/pub/auth'], ['get', 'wechat/mp/auth'], ['post', 'wechat/mp/qrcode'], ['get', 'wechat/pub/jssdk'], ['post', 'wechat/mp/poster'],

        ['post', 'user/login/ticket', 'token'], ['post', 'user/login/quick', 'token'], ['post', 'user/register/quick', 'token'], ['get', 'user/me'],

        ['post', 'ar/targets/search'], ['get', 'vr/tours/{id}'], ['get', 'vr/tours/{id}/xml'],
    ];

    foreach ($registry as $item) {
        $method = $item[0];
        $url = $item[1];
        $function = $item[2] ?? 'api';
        
        $router->{$method}($url, 'ServiceProxyController@' . $function);
    }
});