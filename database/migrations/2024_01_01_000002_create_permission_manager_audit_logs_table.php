<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! class_exists(\Spatie\Permission\Models\Permission::class)
            || ! class_exists(\Spatie\Permission\Models\Role::class)) {
            throw new \RuntimeException(
                'shakeelnasafian/laravel-spatie-permission-manager requires spatie/laravel-permission. '
                . 'Run: composer require spatie/laravel-permission'
            );
        }

        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            throw new \RuntimeException(
                'Spatie "permissions"/"roles" tables do not exist yet. '
                . 'Publish and run Spatie migrations first: '
                . 'php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider" '
                . '&& php artisan migrate'
            );
        }

        if (Schema::hasTable('permission_manager_audit_logs')) {
            return;
        }

        Schema::create('permission_manager_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_name')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_manager_audit_logs');
    }
};
