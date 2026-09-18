<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! class_exists(\Spatie\Permission\Models\Role::class)) {
            throw new \RuntimeException(
                'shakeelnasafian/laravel-spatie-permission-manager requires spatie/laravel-permission. '
                . 'Run: composer require spatie/laravel-permission'
            );
        }

        if (! Schema::hasTable('roles')) {
            throw new \RuntimeException(
                'The Spatie "roles" table does not exist yet. '
                . 'Publish and run Spatie migrations first: '
                . 'php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider" '
                . '&& php artisan migrate'
            );
        }

        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'description')) {
                $table->string('description')->nullable()->after('guard_name');
            }
            if (! Schema::hasColumn('roles', 'hierarchy_level')) {
                $table->unsignedInteger('hierarchy_level')->default(0)->after('description');
            }
            if (! Schema::hasColumn('roles', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('hierarchy_level');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        Schema::table('roles', function (Blueprint $table) {
            $columns = array_filter(
                ['description', 'hierarchy_level', 'is_super_admin'],
                fn (string $col) => Schema::hasColumn('roles', $col)
            );

            if (! empty($columns)) {
                $table->dropColumn(array_values($columns));
            }
        });
    }
};
