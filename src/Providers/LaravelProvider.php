<?php

namespace ZhuiTech\BootLaravel\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use League\Fractal\Manager;
use ZhuiTech\BootLaravel\Console\Commands\PassportInstall;
use ZhuiTech\BootLaravel\Exceptions\AdvancedHandler;
use ZhuiTech\BootLaravel\Middleware\Intranet;
use ZhuiTech\BootLaravel\Middleware\Signature;
use ZhuiTech\BootLaravel\Repositories\RemoteSettingRepository;
use ZhuiTech\BootLaravel\Scheduling\ScheduleRegistry;
use ZhuiTech\BootLaravel\Setting\CacheDecorator;
use ZhuiTech\BootLaravel\Setting\EloquentSetting;
use ZhuiTech\BootLaravel\Setting\SettingInterface;
use ZhuiTech\BootLaravel\Setting\SystemSetting;
use ZhuiTech\BootLaravel\Transformers\ArraySerializer;

/**
 * 通用Laravel项目
 * 
 * @package ZhuiTech\BootLaravel\Providers
 */
class LaravelProvider extends AbstractServiceProvider
{
    protected $providers = [
        'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider',
        'Overtrue\LaravelUploader\UploadServiceProvider',
        'Overtrue\LaravelLang\TranslationServiceProvider',
    ];

    protected $commands = [
        PassportInstall::class
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * 配置Eloquent
         * 解决 MySQL v5.7.7 以下版本会报错误：Specified key was too long error.
         */
        Schema::defaultStringLength(191);

        /**
         * 全局切换语言
         */
        $kernel = app(Kernel::class);
        $kernel->pushMiddleware(\ZhuiTech\BootLaravel\Middleware\Language::class);

        $this->app['router']->aliasMiddleware('intranet', Intranet::class);
        $this->app['router']->aliasMiddleware('sign', Signature::class);

        parent::loadMigrations();
        parent::loadRoutes();
        
        parent::boot();
    }

    /**
     * Register the application services.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function register()
    {
        $this->mergeConfig();
        
        // 异常处理
        $this->app->singleton(ExceptionHandler::class, AdvancedHandler::class);

        // Transformer
        $this->app->bind(Manager::class, function (){
            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            return $manager;
        });

        // 定时任务
        $this->app->singleton(ScheduleRegistry::class);

        // 视图
        $paths = config('view.paths');
        array_unshift($paths, $this->basePath('views'));
        config(['view.paths' => $paths]);
        
        // Setting
        $this->app->singleton(SettingInterface::class, function ($app) {
            $repository = new EloquentSetting(new SystemSetting());
            if (!config('boot-laravel.setting.cache')) {
                return $repository;
            }
            return new CacheDecorator($repository);
        });
        $this->app->alias(SettingInterface::class, 'system_setting');
        
        parent::register();
    }
}
