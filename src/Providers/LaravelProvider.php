<?php

namespace ZhuiTech\BootLaravel\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use League\Fractal\Manager;
use ReflectionException;
use Route;
use ZhuiTech\BootLaravel\Console\Commands\PassportInstall;
use ZhuiTech\BootLaravel\Exceptions\AdvancedHandler;
use ZhuiTech\BootLaravel\Middleware\AdvanceThrottle;
use ZhuiTech\BootLaravel\Middleware\Cache;
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

		/**
		 * 配置Eloquent
		 * 解决 MySQL v5.7.7 以下版本会报错误：Specified key was too long error.
		 * 2020-08-17: 不再设置默认长度，需要索引的字符串字段在定义时设定长度。
		 */
		// Schema::defaultStringLength(191);

		// 全局切换语言
		$kernel = app(Kernel::class);
		$kernel->pushMiddleware(Language::class);

		/* @var Router $router */
		$router = $this->app['router'];
		$router->aliasMiddleware('intranet', Intranet::class);
		$router->aliasMiddleware('sign', Signature::class);
		$router->aliasMiddleware('cache', Cache::class);
		$router->aliasMiddleware('advance.throttle', AdvanceThrottle::class);

		parent::loadMigrations();
		parent::loadRoutes();

		// HOOK接口，不限制请求
		$file = $this->basePath('routes/hook.php');
		Route::prefix(config('boot-laravel.route.api.prefix'))->group($file);

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

		// Transformer
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

		// Setting
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
}
