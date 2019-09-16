<?php
use Illuminate\Routing\Router;

/*
 * 微服务接口
 */
Route::group(['prefix' => 'svc', 'namespace' => 'ZhuiTech\BootLaravel\Controllers'], function (Router $router) {
    $registry = [
        ['get', 'logistics/regions/select'], ['get', 'logistics/regions'],
        ['post', 'sms/verify'], ['post', 'sms/check'],
    ];

    foreach ($registry as $item) {
        $method = $item[0];
        $router->{$method}($item[1], 'ServiceProxyController@api');
    }
});

Route::group(['namespace' => 'ZhuiTech\BootLaravel\Controllers'], function (Router $router) {
    Route::post('staff/auth', 'StaffController@auth');
});