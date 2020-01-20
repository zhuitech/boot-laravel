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
use Session;
use ZhuiTech\BootLaravel\Auth\MemberSessionHandler;
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
    public function boot()
    {
        //$this->configSession();

        parent::boot();
    }

    public function register()
    {
        $this->configAuth();

        parent::register();
    }

    /**
     * 微服务授权机制
     */
    private function configAuth()
    {
        Auth::extend('members', function ($app, $name, array $config) {
            return new RequestGuard(function (Request $request) use ($config) {
                $id = $request->header('X-User');
                $type = $request->header('X-User-Type', 'members');

                if ($id) {
                    $user = new User();
                    $user->id = $id;
                    $user->type = $type;
                    return $user;
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
    }

    /**
     * 微服务会话机制
     */
    private function configSession()
    {
        Session::extend('members', function ($app) {
            $handler = new MemberSessionHandler(
                clone $this->app['cache']->store('redis'),
                $this->app['config']['session.lifetime']
            );

            $handler->getCache()->getStore()->setConnection(
                $this->app['config']['session.connection']
            );

            return $handler;
        });

        config(['session.driver' => 'members']);
    }
}