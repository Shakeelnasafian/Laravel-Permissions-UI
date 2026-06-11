<?php

declare(strict_types=1);

namespace Shakeelnasafian\PermissionManager\Tests\Feature;

use Shakeelnasafian\PermissionManager\Tests\Fixtures\User;
use Shakeelnasafian\PermissionManager\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsUser();
    }

    public function test_sync_roles_assigns_roles_and_logs_the_action(): void
    {
        $target = User::query()->create(['name' => 'Jane', 'email' => 'jane@example.com']);
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $this->post(route('permission-manager.users.sync-roles', $target->id), [
            'role_ids' => [$role->id],
        ])->assertRedirect(route('permission-manager.users.show', $target));

        $this->assertTrue($target->fresh()->hasRole('admin'));

        $this->assertDatabaseHas('permission_manager_audit_logs', [
            'action' => 'assigned',
            'entity_type' => 'user_role',
            'entity_id' => $target->id,
        ]);
    }

    public function test_sync_permissions_assigns_direct_permissions_and_logs_the_action(): void
    {
        $target = User::query()->create(['name' => 'Jane', 'email' => 'jane@example.com']);
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);

        $this->post(route('permission-manager.users.sync-permissions', $target->id), [
            'permission_ids' => [$permission->id],
        ])->assertRedirect(route('permission-manager.users.show', $target));

        $this->assertTrue($target->fresh()->hasPermissionTo('edit posts'));

        $this->assertDatabaseHas('permission_manager_audit_logs', [
            'action' => 'assigned',
            'entity_type' => 'user_permission',
            'entity_id' => $target->id,
        ]);
    }

    public function test_show_returns_404_for_a_missing_user(): void
    {
        $this->get(route('permission-manager.users.show', 99999))->assertNotFound();
    }
}
