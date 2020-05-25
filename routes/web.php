<?php
Route::group(['prefix' => 'assets/svc', 'namespace' => 'ZhuiTech\BootLaravel\Controllers'], function () {
    Route::any('/{wildcard?}', 'ServiceProxyController@api')->where('wildcard', '.+');
});