<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/3/2
 * Time: 21:59
 */

namespace ZhuiTech\BootLaravel\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\RequestGuard;
use Illuminate\Http\Request;
use ZhuiTech\BootLaravel\Models\User;

/**
 * 微服务
 * Class MicroServiceProvider
 * @package ZhuiTech\BootLaravel\Providers
 */
class MicroServiceProvider extends ServiceProvider
{
    public function register()
    {
        /**
         * 配置微服务授权
         */
        Auth::extend('admins', function ($app, $name, array $config) {
            return new RequestGuard(function (Request $request) use ($config) {
                // 从网关传递的用户信息已经验证过，所以直接实例化
                $id = $request->header('X-User');
                $type = $request->header('X-User-Type');
                if ($id && $type == 'admins') {
                    $scopes = $request->header('X-Token-Scopes');
                    return new User([
                        'id' => $id,
                        'type' => $type,
                        'scopes' => $scopes ? explode(',', $scopes) : null,
                        'ip' => $request->header('X-Client-Ip'),
                    ]);
                }
            }, $this->app['request']);
        });
        Auth::extend('members', function ($app, $name, array $config) {
            return new RequestGuard(function (Request $request) use ($config) {
                // 从网关传递的用户信息已经验证过，所以直接实例化
                $id = $request->header('X-User');
                $type = $request->header('X-User-Type');
                if ($id && $type == 'members') {
                    $scopes = $request->header('X-Token-Scopes');
                    return new User([
                        'id' => $id,
                        'type' => $type,
                        'scopes' => $scopes ? explode(',', $scopes) : null,
                        'ip' => $request->header('X-Client-Ip'),
                    ]);
                }
            }, $this->app['request']);
        });
        $auth = array_replace_recursive(config('auth'), [
            'defaults' => [
                'guard' => 'members'
            ],
            'guards' => [
                'members' => [
                    'driver' => 'members',
                ],
                'admins' => [
                    'driver' => 'admins',
                ],
            ],
        ]);
        config(['auth' => $auth]);

        parent::register();
    }
}