<?php
use Illuminate\Routing\Router;

Route::group(['prefix' => 'svc', 'namespace' => 'ZhuiTech\BootLaravel\Controllers'], function (Router $router) {
    $registry = [
        ['get', 'logistics/regions/select'], ['get', 'logistics/regions'], ['get', 'logistics/companies'], ['get', 'logistics/query'],
        ['get', 'system/shipping/query'], ['get', 'system/shipping/company'],

        ['post', 'sms/verify', ['middleware' => ['throttle:20,1']]], ['post', 'sms/check'], ['post', 'sms/send'],

        ['post', 'pay/notify/{channel}'], ['post', 'pay/return/{channel}/{method}'], ['post', 'pay/notify/{channel}/{method}'],

        ['post', 'system/qrcode'], ['get', 'system/qrcode'],
        ['post', 'system/cache/{key}'], ['get', 'system/cache/{key}'],

        ['post', 'file/upload/{strategy}'], ['post', 'file/upload/callback/{disk}'], ['post', 'file/upload/{disk}'], ['post', 'file/upload/form/token'],

        ['get', 'cms/ads/slug/{slug}'],
        ['get', 'cms/page/categories/tree'], ['get', 'cms/page/articles'], ['get', 'cms/page/articles/{id}'], ['get', 'cms/page/types'],

        ['get', 'wechat/pub/auth'], ['get', 'wechat/mp/auth'], ['post', 'wechat/mp/qrcode'], ['get', 'wechat/pub/jssdk'], ['post', 'wechat/mp/poster'],

        ['post', 'user/login/ticket', ['method' => 'token']], ['post', 'user/login/quick', ['method' => 'token']], ['post', 'user/register/quick', ['method' => 'token']], ['get', 'user/me'],

        ['post', 'ar/targets/search'], ['get', 'vr/tours/{id}'], ['get', 'vr/tours/{id}/xml'], ['get', 'vr/tours'], ['get', 'vr/tours/{id}/preview'],
	    
	    ['get', 'device/devices/code/{code}'],
    ];

    foreach ($registry as $item) {
        $method = $item[0];
        $url = $item[1];
        $function = $item[2]['method'] ?? 'api';

        $routeItem = $router->{$method}($url, 'ServiceProxyController@' . $function);

        if (isset($item[2]['middleware'])) {
	        $routeItem->middleware($item[2]['middleware']);
        }
    }
});