<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/3/2
 * Time: 21:59
 */

namespace TrackHub\Laraboot\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\RequestGuard;
use Illuminate\Http\Request;
use TrackHub\Laraboot\Models\User;

class MicroServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();

        $this->registerGuard();
    }

    /**
     * 注册自定义Guard
     */
    protected function registerGuard()
    {
        Auth::extend('proxy', function ($app, $name, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\Guard...
            return new RequestGuard(function (Request $request) use ($config) {
                // 从HTTP头获取网关传递的用户信息
                if ($request->hasHeader('X-User')) {
                    $scopes = $request->header('X-Token-Scopes');
                    return new User([
                        'id' => $request->header('X-User'),
                        'type' => $request->header('X-User-Type'),
                        'scopes' => $scopes ? explode(',', $scopes) : null,
                        'ip' => $request->header('X-Client-Ip'),
                    ]);
                }
            }, $this->app['request']);
        });
    }
}