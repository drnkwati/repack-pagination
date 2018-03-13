<?php

namespace Illuminate\Pagination;

use Illuminate\Support\ServiceProvider;

class PaginationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadViewsFrom(__DIR__ . '/resources/views', 'pagination');

        // framework 5.0 and above
        if (method_exists($this->app, 'basePath') && $this->app->bound('view')) {
            $namespace = 'pagination';
            $viewsPath = __DIR__ . '/resources/views';

            if (is_dir($appPath = $this->app->basePath() . '/resources/views/vendor/' . $namespace)) {
                $this->app['view']->addNamespace($namespace, $appPath);
            }
            // fallback
            $this->app['view']->addNamespace($namespace, $viewsPath);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        Paginator::viewFactoryResolver(function () use ($app) {
            return $app['view'];
        });

        Paginator::currentPathResolver(function () use ($app) {
            return $app['request']->url();
        });

        Paginator::currentPageResolver(function ($pageName = 'page') use ($app) {
            $page = $app['request']->input($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });
    }
}
