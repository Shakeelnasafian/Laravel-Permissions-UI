<?php

declare(strict_types=1);

namespace Shakeelnasafian\PermissionManager\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Shakeelnasafian\PermissionManager\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ServiceProviderTest extends TestCase
{
    public function test_package_config_is_merged(): void
    {
        $this->assertSame('permission-manager', config('permission-manager.route_prefix'));
        $this->assertTrue(config('permission-manager.enable_audit_log'));
    }

    public function test_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('permission-manager.dashboard'));
        $this->assertTrue(Route::has('permission-manager.roles.index'));
        $this->assertTrue(Route::has('permission-manager.permissions.store'));
        $this->assertTrue(Route::has('permission-manager.users.sync-roles'));
        $this->assertTrue(Route::has('permission-manager.audit.index'));
    }

    public function test_views_are_registered_under_package_namespace(): void
    {
        $this->assertTrue(view()->exists('permission-manager::roles.index'));
        $this->assertTrue(view()->exists('permission-manager::permissions.index'));
    }

    public function test_observer_logs_when_a_role_is_created(): void
    {
        Role::create(['name' => 'auditor', 'guard_name' => 'web']);

        $this->assertDatabaseHas('permission_manager_audit_logs', [
            'action' => 'created',
            'entity_type' => 'role',
            'entity_name' => 'auditor',
        ]);
    }

    public function test_observer_logs_when_a_permission_is_created(): void
    {
        Permission::create(['name' => 'publish posts', 'guard_name' => 'web']);

        $this->assertDatabaseHas('permission_manager_audit_logs', [
            'action' => 'created',
            'entity_type' => 'permission',
            'entity_name' => 'publish posts',
        ]);
    }

    public function test_observer_logs_when_a_role_is_deleted(): void
    {
        $role = Role::create(['name' => 'temporary', 'guard_name' => 'web']);
        $role->delete();

        $this->assertDatabaseHas('permission_manager_audit_logs', [
            'action' => 'deleted',
            'entity_type' => 'role',
            'entity_name' => 'temporary',
        ]);
    }
}
