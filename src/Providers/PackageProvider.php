<?php

namespace ZhuiTech\LaraBoot\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use ReflectionClass;

/**
 * 基础服务提供类，封装了所有注册逻辑，在进行包开发的时候，只要添加对应文件或复写对应字段即可自动注册。
 *
 * Class ServiceProvider
 * @package ZhuiTech\LaraBoot\Providers
 */
class PackageProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Package base path.
     *
     * @var string
     */
    protected $basePath;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->basePath = $this->resolveBasePath();

        // 添加migrations目录
        $this->migrations[] = $this->resolvePath('migrations');
    }

    /**
     * Resolve the base path of the package.
     *
     * @return string
     */
    protected function resolveBasePath()
    {
        $filename = (new ReflectionClass(get_class($this)))->getFileName();
        return dirname($filename, 2);
    }

    /**
     * 获取包内文件路径
     * @param $path
     * @return string
     */
    protected function resolvePath($path)
    {
        return $this->basePath.'/'.$path;
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
    public function boot(\Illuminate\Routing\Router $router, \Illuminate\Contracts\Http\Kernel $kernel)
    {
        parent::boot($router, $kernel);

        $this->registerViews();
        $this->overrideViews();

        if (!$this->app->routesAreCached()) {
            $this->mapWebRoutes();
            $this->mapApiRoutes();
            $this->mapAdminRoutes();
        }

        if ($this->app->runningInConsole()) {
            $this->publishAssets();
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /* -----------------------------------------------------------------
     |  Register function
     | -----------------------------------------------------------------
     */

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        $path = $this->resolvePath('routes/web.php');

        if (file_exists($path)) {
            Route::prefix('')
                ->middleware(['web'])
                ->namespace($this->namespace)
                ->group($path);
        }
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapAdminRoutes()
    {
        $path = $this->resolvePath('routes/admin.php');

        if (file_exists($path)) {
            Route::prefix('admin')
                ->middleware(['web', 'auth.admin:admin'])
                ->namespace($this->namespace)
                ->group($path);
        }
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        $path = $this->resolvePath('routes/api.php');

        if (file_exists($path)) {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group($path);
        }
    }

    /**
     * 注册视图
     */
    protected function registerViews()
    {
        $path = $this->resolvePath('resources/views');

        if (file_exists($path) && is_dir($path)) {
            $this->loadViewsFrom($path, File::name($this->basePath));
        }
    }

    /**
     * 复写视图
     */
    protected function overrideViews()
    {
        $path = $this->resolvePath('resources/views-override');

        if (file_exists($path) && is_dir($path)) {
            $dirs = File::directories($path);

            foreach ($dirs as $dir) {
                View::prependNamespace(File::name($dir), $dir);
            }
        }
    }

    /**
     * 发布资源
     */
    protected function publishAssets()
    {
        $path = $this->resolvePath('resources/assets');

        if (file_exists($path) && is_dir($path)) {
            $this->publishes([$path => public_path('assets/' . File::name($this->basePath))], 'public');
        }
    }
}
