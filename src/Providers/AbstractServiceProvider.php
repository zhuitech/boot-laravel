<?php

namespace ZhuiTech\BootLaravel\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use ReflectionClass;
use Illuminate\Foundation\AliasLoader;

/**
 * 基础服务提供类，封装了所有注册逻辑。
 *
 * Class ServiceProvider
 * @package ZhuiTech\BootLaravel\Providers
 */
abstract class AbstractServiceProvider extends BaseServiceProvider
{
    /**
     * 注册档案安装器
     * @var array
     */
    protected $installers = [];

    /**
     * 注册服务提供者
     * @var array
     */
    protected $providers = [];

    /**
     * 注册容器别名
     * @var array
     */
    protected $aliases = [];

    /**
     * 注册门面别名
     * @var array
     */
    protected $facades = [];

    /**
     * 注册数据库
     * @var array
     */
    protected $migrations = [];

    /**
     * 注册命令
     * @var array
     */
    protected $commands = [];

    /**
     * 注册配置目录
     * @var array
     */
    protected $configures = [];

    /**
     * 错误代码
     * 用配置文件复写errors会导致索引重排，所以在provider中提供一个扩展的入口
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Resolve the base path of the package.
     *
     * @param null $path
     * @return string
     * @throws \ReflectionException
     */
    protected function basePath($path = NULL)
    {
        $filename = (new ReflectionClass(get_class($this)))->getFileName();
        return dirname($filename, 3) . "/" . $path;
    }

    /* -----------------------------------------------------------------
     |  Main
     | -----------------------------------------------------------------
     */

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands($this->commands);
        $this->registerErrors();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerInstallers();
        $this->registerAliases();
        $this->registerFacades();
        $this->registerProviders();
    }

    /* -----------------------------------------------------------------
     |  自动注册
     | -----------------------------------------------------------------
     */

    /**
     * 注册档案安装器
     */
    protected function registerInstallers()
    {
        $this->app->tag($this->installers, 'profile_installers');
    }

    /**
     * 注册服务提供者
     */
    protected function registerProviders()
    {
        foreach ($this->providers as $key => $provider) {
            if (class_exists(is_string($key) ? $key : $provider)) {
                $this->app->register($provider);
            }
        }
    }

    /**
     * 注册别名
     */
    protected function registerAliases()
    {
        foreach ($this->aliases as $name => $class){
            $this->app->alias($class, $name);
        }
    }

    /**
     * 注册门面别名
     */
    protected function registerFacades()
    {
        if (!empty($this->facades)) {
            AliasLoader::getInstance($this->facades)->register();
        }
    }

    /**
     * 注册错误代码
     */
    protected function registerErrors()
    {
        if (!empty($this->errors)) {
            $config = config('boot-laravel');
            $config['errors'] += $this->errors;
            config(['boot-laravel' => $config]);
        }
    }

    /* -----------------------------------------------------------------
     |  手动注册
     | -----------------------------------------------------------------
     */

    /**
     * 注册数据库
     */
    protected function loadMigrations()
    {
        $path = $this->basePath('migrations');

        if (file_exists($path) && is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    /**
     * 注册配置文件
     */
    protected function mergeConfig()
    {
        $path = $this->basePath('config');

        if (file_exists($path) && is_dir($path)) {
            $files = File::files($path);

            foreach ($files as $file) {
                $this->mergeConfigFrom($file, File::name($file));
            }
        }
    }

    /**
     * 注册路由
     */
    protected function loadRoutes()
    {
        if ($this->app->routesAreCached()) {
            $this->app->booted(function () {
                require $this->app->getCachedRoutesPath();
            });
        }
        else {
            $file = $this->basePath('routes/api.php');
            if (file_exists($file)) {
                Route::prefix('api')->middleware('api')->group($file);
            }

            $file = $this->basePath('routes/web.php');
            if (file_exists($file)) {
                Route::prefix('')->middleware('web')->group($file);
            }

            $this->app->booted(function () {
                $this->app['router']->getRoutes()->refreshNameLookups();
                $this->app['router']->getRoutes()->refreshActionLookups();
            });
        }
    }
}
