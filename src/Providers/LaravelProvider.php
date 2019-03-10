<?php

namespace ZhuiTech\BootLaravel\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Symfony\Component\HttpKernel\HttpKernel;
use ZhuiTech\BootLaravel\Console\Commands\ProfileInstall;
use ZhuiTech\BootLaravel\Console\Commands\ProfileList;
use ZhuiTech\BootLaravel\Exceptions\AdvancedHandler;
use ZhuiTech\BootLaravel\Profiles\SettingsInstaller;
use ZhuiTech\BootLaravel\Providers\PackageServiceProvider;
use ZhuiTech\BootLaravel\Providers\ServiceProvider;
use Illuminate\Support\Facades\View;
use ZhuiTech\BootLaravel\Repositories\RemoteSettingRepository;

class LaravelProvider extends ServiceProvider
{
    protected $providers = [
        'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider',
        'Laracasts\Flash\FlashServiceProvider',
        'Overtrue\LaravelUploader\UploadServiceProvider',
        'Overtrue\LaravelLang\TranslationServiceProvider',
    ];

    protected $namespace = 'ZhuiTech\BootLaravel\Controllers';

    protected $commands = [
        ProfileInstall::class,
        ProfileList::class
    ];

    protected $installers = [
        SettingsInstaller::class
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

        parent::boot();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * 默认配置
         */
        $this->configures[] = $this->basePath('config/micro-services.php');
        $this->configures[] = $this->basePath('config/ide-helper.php');
        $this->configures[] = $this->basePath('config/boot-laravel.php');

        /**
         * 自定义Exception
         */
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            AdvancedHandler::class
        );

        /**
         * 默认视图
         */
        $paths = config('view.paths');
        array_unshift($paths, $this->basePath('views'));
        config(['view.paths' => $paths]);

        /**
         * 配置
         */
        $this->app->singleton('setting', function () {
            return new RemoteSettingRepository();
        });

        parent::register();
    }
}
