<?php

namespace ZhuiTech\BootLaravel\Providers;

use Illuminate\Support\Facades\Schema;
use ZhuiTech\BootLaravel\Console\Commands\KeyGenerateCommand;
use ZhuiTech\BootLaravel\Exceptions\LumenHandler;
use ZhuiTech\BootLaravel\Providers\ServiceProvider;

class LumenProvider extends ServiceProvider
{
    protected $providers = [
        'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider',
    ];

    protected $commands = [
        KeyGenerateCommand::class
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
        $this->configures[] = $this->basePath('config/micro-services.php');
        $this->configures[] = $this->basePath('config/ide-helper.php');
        $this->configures[] = $this->basePath('config/boot-laravel.php');

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \ZhuiTech\BootLaravel\Exceptions\LumenHandler::class
        );

        parent::register();
    }
}
