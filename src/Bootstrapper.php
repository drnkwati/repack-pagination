<?php

namespace Repack\Pagination;

use ArrayAccess;

class Bootstrapper
{
    public static function bootstrap(ArrayAccess $ioc)
    {
        !method_exists($ioc, 'booted') ?: static::booted($ioc);

        Paginator::viewFactoryResolver(function () use ($ioc) {
            return $ioc['view'];
        });

        Paginator::currentPathResolver(function () use ($ioc) {
            return $ioc['httpRequest']->url();
            // NB:
            // You must create a pagination and usage set the base path to assign to all URLs.
            // $pager->setPath($pager->resolveCurrentPath());
            // $pager->withPath($pager->resolveCurrentPath());
            //
            // $users->onEachSide(5)->links()
        });

        Paginator::currentPageResolver(function ($pageName = 'page') use ($ioc) {
            $page = $ioc['httpRequest']->input($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public static function booted(ArrayAccess $ioc)
    {
        $ioc->booted(function ($ioc) {
            Bootstrapper::loadViewsFrom($source = __DIR__ . '/resources/views', 'pagination', $ioc);
            // check if views exists in resourcePath
            $target = $ioc->resourcePath('views/vendor/pagination');
            if (!is_dir($target) && @mkdir($target, 0755, true)) {
                $ioc['files']->copyDirectory($source, $target);
            }
        });
    }

    /**
     * Register a view file namespace.
     *
     * @param  string|array  $path
     * @param  string  $namespace
     * @return void
     */
    public static function loadViewsFrom($path, $namespace, ArrayAccess $ioc)
    {
        if (is_array($ioc['config']['view']['paths'])) {
            foreach ($ioc['config']['view']['paths'] as $viewPath) {
                if (is_dir($appPath = $viewPath . '/vendor/' . $namespace)) {
                    $ioc['view']->addNamespace($namespace, $appPath);
                }
            }
        }

        $ioc['view']->addNamespace($namespace, $path);
    }
}
