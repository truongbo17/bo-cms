<?php

namespace Bo\PageManager;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Route;

class PageManagerServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Where the route file lives, both inside the package and in the app (if overwritten).
     *
     * @var string
     */
    public $routeFilePath = '/routes/pagemanager.php';

    private string $migrationFilePath = '/database/migrations';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . $this->migrationFilePath);

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__ . '/config/pagemanager.php',
            'bo.pagemanager'
        );

        $this->loadViewsFrom(realpath(__DIR__ . '/resources/views/vendor/bo/crud'), 'pagemanager');

        $this->loadTranslationsFrom(realpath(__DIR__ . '/resources/lang'), 'pagemanager');

        \SideBarDashBoard::registerItem('page_manager')
            ->setLabel('Page')
            ->setPosition(1)
            ->setRoute(bo_url('page'))
            ->setIcon('nav-icon la la-file-o')
            ->render();
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        // by default, use the routes file provided in vendor
        $routeFilePathInUse = __DIR__.$this->routeFilePath;

        // but if there's a file with the same name in routes/backpack, use that one
        if (file_exists(base_path().$this->routeFilePath)) {
            $routeFilePathInUse = base_path().$this->routeFilePath;
        }

        $this->loadRoutesFrom($routeFilePathInUse);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->setupRoutes($this->app->router);
    }
}
