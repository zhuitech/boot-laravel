<?php

namespace TrackHub\Laraboot\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use TrackHub\Laraboot\Console\Commands\ProfileInstall;
use TrackHub\Laraboot\Console\Commands\ProfileList;
use TrackHub\Laraboot\Exceptions\AdvancedHandler;
use TrackHub\Laraboot\Profiles\SettingsInstaller;
use TrackHub\Laraboot\Providers\PackageServiceProvider;
use TrackHub\Laraboot\Providers\ServiceProvider;
use Illuminate\Support\Facades\View;

class LaravelProvider extends ServiceProvider
{
    protected $providers = [
        'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider',
        'Laracasts\Flash\FlashServiceProvider',
        'Overtrue\LaravelUploader\UploadServiceProvider',
    ];

    protected $namespace = 'TrackHub\Laraboot\Controllers';

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
        $this->configures[] = $this->basePath('config/laraboot.php');

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

        parent::register();
    }
}
