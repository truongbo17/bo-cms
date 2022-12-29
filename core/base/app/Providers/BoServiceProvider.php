<?php

namespace Bo\Base\Providers;

use Bo\Base\Http\Middleware\ThrottlePasswordRecovery;
use Bo\Base\Library\CrudPanel\CrudPanel;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class BoServiceProvider extends ServiceProvider
{

    protected array $commands = [
        \Bo\Base\Console\Commands\Install::class,
        \Bo\Base\Console\Commands\AddSidebarContent::class,
        \Bo\Base\Console\Commands\AddCustomRouteContent::class,
        \Bo\Base\Console\Commands\Version::class,
        \Bo\Base\Console\Commands\CreateUser::class,
        \Bo\Base\Console\Commands\PublishView::class,
        \Bo\Base\Console\Commands\Fix::class,
    ];

    // Indicates if loading of the provider is deferred.
    protected bool $defer = false;
    // Where the route file lives, both inside the package and in the app (if overwritten).
    public string $routeFilePath = '/routes/base.php';
    // Where custom routes can be written, and will be registered by bo.
    public string $customRoutesFilePath = '/routes/custom.php';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->loadViewsWithFallbacks();
        $this->loadTranslationsFrom(realpath(__DIR__ . '/../../resources/lang'), 'bo');
        $this->loadConfigs();
        $this->registerMiddlewareGroup($this->app->router);
        $this->setupRoutes($this->app->router);
        $this->setupCustomRoutes($this->app->router);
        $this->publishFiles();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // load the macros
        include_once __DIR__ . '/../../macros.php';

        // Bind the CrudPanel object to Laravel's service container
        $this->app->singleton('crud', function ($app) {
            return new CrudPanel($app);
        });

        // Bind the widgets collection object to Laravel's service container
        $this->app->singleton('widgets', function ($app) {
            return new Collection();
        });

        // register the helper functions
        $this->loadHelpers();

        // register the artisan commands
        $this->commands($this->commands);
    }

    public function registerMiddlewareGroup(Router $router)
    {
        $middleware_key = config('bo.base.middleware_key');
        $middleware_class = config('bo.base.middleware_class');

        if (!is_array($middleware_class)) {
            $router->pushMiddlewareToGroup($middleware_key, $middleware_class);

            return;
        }

        foreach ($middleware_class as $middleware) {
            $router->pushMiddlewareToGroup($middleware_key, $middleware);
        }

        // register internal bo middleware for throttling the password recovery functionality
        // but only if functionality is enabled by developer in config
        if (config('bo.base.setup_password_recovery_routes')) {
            $router->aliasMiddleware('bo.throttle.password.recovery', ThrottlePasswordRecovery::class);
        }
    }

    public function publishFiles()
    {
        $error_views = [__DIR__ . '/../../resources/error_views' => resource_path('views/errors')];
        $bo_views = [__DIR__ . '/../../resources/views' => resource_path('views/vendor/bo')];
        $bo_public_assets = [__DIR__ . '/../../public' => public_path()];
        $bo_lang_files = [__DIR__ . '/../../resources/lang' => app()->langPath() . '/vendor/bo'];

        // calculate the path from current directory to get the vendor path
        $vendorPath = dirname(__DIR__, 4);
        $gravatar_assets = [$vendorPath . '/vendor/creativeorange/gravatar/config' => config_path()];

        // establish the minimum amount of files that need to be published, for Bo to work; there are the files that will be published by the install command
        $minimum = array_merge(
//            $bo_views,
            $bo_lang_files,
            $error_views,
            $bo_public_assets,
            $gravatar_assets
        );

        // register all possible publish commands and assign tags to each
        $this->publishes($bo_lang_files, 'lang');
//        $this->publishes($bo_views, 'views');
        $this->publishes($error_views, 'errors');
        $this->publishes($bo_public_assets, 'public');
        $this->publishes($gravatar_assets, 'gravatar');
        $this->publishes($minimum, 'minimum');
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        $routeFilePathInUse = __DIR__ . "/../../" . $this->routeFilePath;

        if (file_exists($routeFilePathInUse)) {
            $this->loadRoutesFrom($routeFilePathInUse);
        }
    }

    /**
     * Load custom routes file.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    public function setupCustomRoutes(Router $router)
    {
        $routeFilePathInUse = __DIR__ . "/../../" . $this->customRoutesFilePath;

        if (file_exists($routeFilePathInUse)) {
            $this->loadRoutesFrom($routeFilePathInUse);
        }
    }

    public function loadViewsWithFallbacks()
    {
        $customBaseFolder = resource_path('views/vendor/bo/base');
        $customCrudFolder = resource_path('views/vendor/bo/crud');

        // - first the published/overwritten views (in case they have any changes)
        if (file_exists($customBaseFolder)) {
            $this->loadViewsFrom($customBaseFolder, 'bo');
        }
        if (file_exists($customCrudFolder)) {
            $this->loadViewsFrom($customCrudFolder, 'crud');
        }
        // - then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom(realpath(__DIR__ . '/../../resources/views/base'), 'bo');
        $this->loadViewsFrom(realpath(__DIR__ . '/../../resources/views/crud'), 'crud');
    }

    protected function mergeConfigFromOperationsDirectory()
    {
        $operationConfigs = scandir(__DIR__ . '/../../config/bo/operations/');
        $operationConfigs = array_diff($operationConfigs, ['.', '..']);

        if (!count($operationConfigs)) {
            return;
        }

        foreach ($operationConfigs as $configFile) {
            $this->mergeConfigFrom(
                __DIR__ . '/../../config/bo/operations/' . $configFile,
                'bo.operations.' . substr($configFile, 0, strrpos($configFile, '.'))
            );
        }
    }

    public function loadConfigs()
    {
        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(__DIR__ . '/../../config/bo/crud.php', 'bo.crud');
        $this->mergeConfigFrom(__DIR__ . '/../../config/bo/base.php', 'bo.base');
        $this->mergeConfigFromOperationsDirectory();

        // add the root disk to filesystem configuration
        app()->config['filesystems.disks.' . config('bo.base.root_disk_name')] = [
            'driver' => 'local',
            'root'   => base_path(),
        ];

        // add the core disk to filesystem configuration
        app()->config['filesystems.disks.' . config('bo.base.core_disk_name')] = [
            'driver' => 'local',
            'root'   => config('bo.base.path_core_base'),
        ];

        // add the application disk to filesystem configuration (plugins and themes)
        app()->config['filesystems.disks.' . config('bo.base.application_disk_name')] = [
            'driver' => 'local',
            'root'   => config('bo.base.path_application_base'),
        ];

        // add the bo_users authentication provider to the configuration
        app()->config['auth.providers'] = app()->config['auth.providers'] +
            [
                'bo' => [
                    'driver' => 'eloquent',
                    'model'  => config('bo.base.user_model_fqn'),
                ],
            ];

        // add the bo_users password broker to the configuration
        app()->config['auth.passwords'] = app()->config['auth.passwords'] +
            [
                'bo' => [
                    'provider' => 'bo',
                    'table'    => 'password_resets',
                    'expire'   => 60,
                    'throttle' => config('bo.base.password_recovery_throttle_notifications'),
                ],
            ];

        // add the bo_users guard to the configuration
        app()->config['auth.guards'] = app()->config['auth.guards'] +
            [
                'bo' => [
                    'driver'   => 'session',
                    'provider' => 'bo',
                ],
            ];
    }

    /**
     * Load the Bo helper methods, for convenience.
     */
    public function loadHelpers()
    {
        require_once __DIR__ . '/../../helpers.php';
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['crud', 'widgets'];
    }
}
