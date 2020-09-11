<?php

namespace ZhuiTech\BootLaravel\Providers;

use Auth;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use League\Fractal\Manager;
use ReflectionException;
use Route;
use ZhuiTech\BootLaravel\Console\Commands\PassportInstall;
use ZhuiTech\BootLaravel\Exceptions\AdvancedHandler;
use ZhuiTech\BootLaravel\Guards\JwtTokenGuard;
use ZhuiTech\BootLaravel\Middleware\PrimaryThrottle;
use ZhuiTech\BootLaravel\Middleware\SecondaryThrottle;
use ZhuiTech\BootLaravel\Middleware\PageCache;
use ZhuiTech\BootLaravel\Middleware\Intranet;
use ZhuiTech\BootLaravel\Middleware\Language;
use ZhuiTech\BootLaravel\Middleware\Signature;
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
	 * @throws ReflectionException
	 */
	public function boot()
	{
		// 加载设置
		$this->loadSettings();

		// 加载数据库
		parent::loadMigrations();

		// 配置中间件
		/* @var Router $router */
		$router = $this->app['router'];
		$kernel = app(Kernel::class);
		$kernel->pushMiddleware(Language::class);
		$router->aliasMiddleware('intranet', Intranet::class);
		$router->aliasMiddleware('sign', Signature::class);
		$router->aliasMiddleware('cache', PageCache::class);
		$router->aliasMiddleware('throttle1', PrimaryThrottle::class);
		$router->aliasMiddleware('throttle2', SecondaryThrottle::class);

		// 加载路由
		parent::loadRoutes();

		// 配置授权
		$this->configAuth();

		parent::boot();
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function register()
	{
		$this->mergeConfig();

		// 异常处理
		$this->app->singleton(ExceptionHandler::class, AdvancedHandler::class);

		// 转化器
		$this->app->bind(Manager::class, function () {
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

		// 系统设置
		$this->app->singleton(SettingInterface::class, function ($app) {
			$repository = new EloquentSetting(new SystemSetting());
			if (!config('boot-laravel.setting.cache')) {
				return $repository;
			}
			return new CacheDecorator($repository);
		});
		$this->app->alias(SettingInterface::class, 'system_setting');

		// 加载动态模块
		if (!empty(config('boot-laravel.load_modules'))) {
			$modules = config('boot-laravel.modules');
			$load_modules = explode(',', config('boot-laravel.load_modules'));
			foreach ($load_modules as $name) {
				if (isset($modules[$name])) {
					$this->providers[] = $modules[$name];
				}
			}
		}

		parent::register();
	}

	/**
	 * 加载已经修改的配置
	 */
	private function loadSettings()
	{
		$settings = settings()->allToArray();
		foreach ($settings as $key => $value) {
			if (Str::contains($key, '.')) {
				config([$key => $value]);
			}
		}
	}

	/**
	 * 配置授权机制
	 */
	private function configAuth()
	{
		/*
		 * 弱授权，对于一些安全性要求不高的接口，可以直接信任JWT令牌，不做数据库查询，
		 * 在 config/auth.php 配置后方可使用
		 */
		Auth::extend('jwt', function ($app, $name, array $config) {
			return new RequestGuard(function ($request) use ($config) {
				$user = (new JwtTokenGuard())->user($request);
				return $user;
			}, $this->app['request']);
		});

		$auth = config('auth');
		$auth['guards']['jwt'] = [
			'driver' => 'jwt'
		];
		config(Arr::dot($auth, 'auth.'));
	}
}
