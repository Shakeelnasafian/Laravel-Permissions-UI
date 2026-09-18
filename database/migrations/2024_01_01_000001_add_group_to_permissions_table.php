<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! class_exists(\Spatie\Permission\Models\Permission::class)) {
            throw new \RuntimeException(
                'shakeelnasafian/laravel-spatie-permission-manager requires spatie/laravel-permission. '
                . 'Run: composer require spatie/laravel-permission'
            );
        }

        if (! Schema::hasTable('permissions')) {
            throw new \RuntimeException(
                'The Spatie "permissions" table does not exist yet. '
                . 'Publish and run Spatie migrations first: '
                . 'php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider" '
                . '&& php artisan migrate'
            );
        }

        if (Schema::hasColumn('permissions', 'group')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('group')->nullable()->after('guard_name')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        if (! Schema::hasColumn('permissions', 'group')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
