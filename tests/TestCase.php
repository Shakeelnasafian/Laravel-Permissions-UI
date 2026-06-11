<?php

declare(strict_types=1);

namespace Shakeelnasafian\PermissionManager\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Shakeelnasafian\PermissionManager\PermissionManagerServiceProvider;
use Shakeelnasafian\PermissionManager\Tests\Fixtures\User;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            PermissionServiceProvider::class,
            PermissionManagerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $config = $app['config'];

        $config->set('database.default', 'testing');
        $config->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $config->set('auth.providers.users.model', User::class);
        $config->set('permission-manager.user_model', User::class);

        // Use the real "web" + "auth" stack: "web" provides the session and
        // shared $errors bag that the views rely on. CSRF is skipped
        // automatically while running tests, so no token plumbing is needed.
        $config->set('permission-manager.route_middleware', ['web', 'auth']);
    }

    /**
     * Build the Spatie tables and a minimal users table, then run the
     * package's own migrations (group column, audit log, role columns).
     */
    protected function setUpDatabase(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
        });

        $spatieMigration = include __DIR__
            . '/../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';
        $spatieMigration->up();

        $this->artisan('migrate')->run();
    }

    protected function actingAsUser(array $attributes = []): User
    {
        $user = User::query()->create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ], $attributes));

        $this->actingAs($user);

        return $user;
    }
}
