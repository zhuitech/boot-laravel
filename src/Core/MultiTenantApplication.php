<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 17/8/31
 * Time: 12:03
 */

namespace ZhuiTech\LaraBoot\Core;
use Illuminate\Foundation\Laraboot\LoadEnvironmentVariables;
use Illuminate\Http\Request;

/**
 * 实现多租户模式
 *
 * Class MultiTenantApplication
 * @package ZhuiTech\LaraBoot\Core
 */
class MultiTenantApplication extends CompatibilityApplication
{
    protected function registerBaseServiceProviders()
    {
        $this->registerTenant();
        parent::registerBaseServiceProviders();
    }

    /**
     * 注册租户
     *
     * @throws \Exception
     */
    protected function registerTenant()
    {
        /**
         * 加载所有租户
         */
        $tenants = include base_path('storage/tenants.php');

        /**
         * 根据环境变量选取租户
         */
        if ($this->runningInConsole()) {
            $name = env('TENANT');
            if (isset($tenants[$name])) {
                $tenant = $tenants[$name];
            }
        }
        /**
         * 根据全局变量选取租户
         */
        elseif (isset($GLOBALS['TENANT'])) {
            $name = $GLOBALS['TENANT'];
            if (isset($tenants[$name])) {
                $tenant = $tenants[$name];
            }
        }
        /**
         * 根据访问域名选取租户
         */
        else {
            // 配置代理服务器，获取正确的客户端信息
            (new LoadEnvironmentVariables())->bootstrap($this);
            Request::setTrustedProxies([env('TRUSTED_PROXIES')]);

            $request = Request::createFromGlobals();
            $domain = $request->getHost();

            foreach ($tenants as $item) {
                if ($item['domain'] == $domain) {
                    $tenant = $item;
                    break;
                }
            }
        }

        /**
         * 提供一个默认的租户
         */
        if (empty($tenant)) {
            $tenant = $tenants['demo'];
        }

        /**
         * 注册到容器
         */
        $this->instance('tenant', $tenant);
        $this->useStoragePath($this->basePath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.$tenant['name']);
    }
}