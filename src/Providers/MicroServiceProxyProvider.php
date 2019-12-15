<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2018/3/2
 * Time: 21:59
 */

namespace ZhuiTech\BootLaravel\Providers;

use Illuminate\Auth\RequestGuard;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\ResourceServer;
use ZhuiTech\BootLaravel\Auth\MemberUserProvider;
use ZhuiTech\BootLaravel\Auth\TokenGuard;
use ZhuiTech\BootLaravel\Models\TokenUser;

/**
 * 微服务代理
 *
 * Class MicroServiceProvider
 * @package ZhuiTech\BootLaravel\Providers
 */
class MicroServiceProxyProvider extends AbstractServiceProvider
{
    public function register()
    {
        $this->configAuth();

        parent::register();
    }

    /**
     * 调用方授权机制
     */
    private function configAuth()
    {
        Auth::provider('members', function ($app, array $config) {
            return new MemberUserProvider();
        });

        Auth::extend('passport', function ($app, $name, array $config) {
            $guard = new RequestGuard(function ($request) use ($config) {
                return (new TokenGuard(
                    $this->app->make(ResourceServer::class),
                    $config['provider'],
                    $this->app->make(TokenRepository::class),
                    $this->app->make(ClientRepository::class),
                    $this->app->make('encrypter')
                ))->user($request);
            }, $this->app['request']);

            return tap($guard, function ($guard) {
                $this->app->refresh('request', $guard, 'setRequest');
            });
        });

        $auth = [
            'defaults' => [
                'guard' => 'members'
            ],
            'guards' => [
                'members' => [
                    'driver' => 'passport',
                    'provider' => 'members',
                ],
            ],
            'providers' => [
                'members' => [
                    'driver' => 'members',
                    'model' => TokenUser::class
                ],
            ],
        ];
        config(Arr::dot($auth, 'auth.'));
    }
}