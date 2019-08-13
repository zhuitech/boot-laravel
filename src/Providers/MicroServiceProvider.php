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
use Illuminate\Support\Arr;

/**
 * 微服务
 *
 * Class MicroServiceProvider
 * @package ZhuiTech\BootLaravel\Providers
 */
class MicroServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        /**
         * 配置微服务授权
         */
        Auth::extend('members', function ($app, $name, array $config) {
            return new RequestGuard(function (Request $request) use ($config) {
                $id = $request->header('X-User');
                $type = $request->header('X-User-Type', 'members');

                if ($id) {
                    return new User([
                        'id' => $id,
                        'type' => $type,
                    ]);
                }
            }, $this->app['request']);
        });

        $auth = [
            'defaults' => [
                'guard' => 'members'
            ],
            'guards' => [
                'members' => [
                    'driver' => 'members',
                ],
            ],
        ];
        config(Arr::dot($auth, 'auth.'));

        parent::register();
    }
}