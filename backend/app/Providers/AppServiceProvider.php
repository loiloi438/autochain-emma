<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->resolved(Router::class)) {
            /** @var Router $router */
            $router = $this->app->make(Router::class);
            $router->aliasMiddleware('role', RoleMiddleware::class);
            $router->aliasMiddleware('permission', PermissionMiddleware::class);
            $router->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
        }
    }
}
