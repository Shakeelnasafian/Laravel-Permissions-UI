<?php

declare(strict_types=1);

namespace Shakeelnasafian\PermissionManager;

use Illuminate\Support\ServiceProvider;
use Shakeelnasafian\PermissionManager\Observers\PermissionManagerObserver;

class PermissionManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/permission-manager.php', 'permission-manager');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'permission-manager');

        if ($this->spatieIsInstalled()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->publishes([
            __DIR__ . '/../config/permission-manager.php' => config_path('permission-manager.php'),
        ], 'permission-manager-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/permission-manager'),
        ], 'permission-manager-views');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'permission-manager-migrations');

        if (config('permission-manager.enable_audit_log', true) && $this->spatieIsInstalled()) {
            \Spatie\Permission\Models\Role::observe(PermissionManagerObserver::class);
            \Spatie\Permission\Models\Permission::observe(PermissionManagerObserver::class);
        }
    }

    private function spatieIsInstalled(): bool
    {
        return class_exists(\Spatie\Permission\Models\Role::class)
            && class_exists(\Spatie\Permission\Models\Permission::class);
    }
}
