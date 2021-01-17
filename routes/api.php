<?php
use Illuminate\Routing\Router;

Route::group(['prefix' => 'svc', 'namespace' => 'ZhuiTech\BootLaravel\Controllers'], function (Router $router) {
    $registry = [

	    ['post', 'user/login/ticket', ['func' => 'token']],
	    ['post', 'user/login/pass', ['func' => 'token']],
	    ['post', 'user/login/sms', ['func' => 'token']],
	    ['get', 'user/me'],
	    ['post', 'user/update/password'],
	    ['post', 'user/update/mobile'],
	    ['post', 'user/update/profile'],
	    ['get', 'user/my/connects'],
	    ['delete', 'user/my/connects/{id}'],
	    ['get|post', 'user/my/addresses'],
	    ['get|put|delete', 'user/my/addresses/{id}'],
	    ['get|post', 'user/my/invoices'],
	    ['get|put|delete', 'user/my/invoices/{id}'],
	    ['get|post', 'user/my/bank-accounts'],
	    ['get|put|delete', 'user/my/bank-accounts/{id}'],

	    ['post', 'sms/verify', ['middleware' => ['throttle2:10,1']]],
	    ['post', 'sms/check'],
	    ['post', 'sms/send', ['middleware' => ['throttle2:10,1']]],

	    ['get', 'message/my/notifications/unread'],
	    ['post', 'message/my/notifications/mark'],
	    ['post', 'message/my/notifications/delete'],
	    ['get', 'message/my/notifications'],

	    ['get', 'logistics/regions/select'],
	    ['get', 'logistics/regions'],
	    ['get', 'logistics/companies'],
	    ['get', 'logistics/query'],
        ['get', 'system/shipping/query'],
	    ['get', 'system/shipping/company'],

	    ['get', 'pay/banks'],
        ['post', 'pay/notify/{channel}'],
	    ['post', 'pay/return/{channel}/{method}'],
	    ['post', 'pay/notify/{channel}/{method}'],

        ['get|post', 'system/qrcode'],
        ['get|post', 'system/cache/{key}'],

        ['post', 'file/upload/{strategy}'],
	    ['post', 'file/upload/callback/{disk}'],
	    ['post', 'file/upload/{disk}'],
	    ['post', 'file/upload/form/token'],

        ['get', 'cms/ads/slug/{slug}', ['middleware' => ['cache:ads.{slug},0']]],
	    ['get', 'cms/ads', ['middleware' => ['cache:ads,0']]],
        ['get', 'cms/page/categories/tree'],
	    ['get', 'cms/page/articles'],
	    ['get', 'cms/page/articles/{id}'],
	    ['get', 'cms/page/types'],

        ['get', 'wechat/pub/auth'],
	    ['get', 'wechat/mp/auth', ['func' => 'token']],
	    ['post', 'wechat/mp/qrcode'],
	    ['get', 'wechat/pub/jssdk'],
	    ['post', 'wechat/mp/poster'],

        ['post', 'ar/targets/search'],
	    ['get', 'vr/tours/{id}'],
	    ['get', 'vr/tours/{id}/xml'],
	    ['get', 'vr/tours'],
	    ['get', 'vr/tours/{id}/preview'],
	    
	    ['get', 'device/devices/code/{code}'],
    ];

    foreach ($registry as $item) {
        $methods = explode('|', $item[0]);
        $url = $item[1];
        $function = $item[2]['func'] ?? 'api';

        foreach ($methods as $method) {
	        $routeItem = $router->{$method}($url, 'ServiceProxyController@' . $function);
	        if (isset($item[2]['middleware'])) {
		        $routeItem->middleware($item[2]['middleware']);
	        }
        }
    }
});