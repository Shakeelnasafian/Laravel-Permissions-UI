<?php

declare(strict_types=1);

namespace Shakeelnasafian\PermissionManager\Tests\Feature;

use Shakeelnasafian\PermissionManager\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsUser();
    }

    public function test_store_creates_a_role_with_extra_attributes(): void
    {
        $response = $this->post(route('permission-manager.roles.store'), [
            'name' => 'editor',
            'description' => 'Edits content',
            'hierarchy_level' => 5,
            'is_super_admin' => true,
        ]);

        $role = Role::query()->where('name', 'editor')->first();

        $this->assertNotNull($role);
        $response->assertRedirect(route('permission-manager.roles.show', $role));

        $this->assertDatabaseHas('roles', [
            'name' => 'editor',
            'description' => 'Edits content',
            'hierarchy_level' => 5,
            'is_super_admin' => true,
        ]);
    }

    public function test_store_rejects_a_duplicate_name_in_the_same_guard(): void
    {
        Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $this->from(route('permission-manager.roles.create'))
            ->post(route('permission-manager.roles.store'), ['name' => 'editor'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Role::query()->where('name', 'editor')->count());
    }

    public function test_destroy_deletes_the_role(): void
    {
        $role = Role::create(['name' => 'removable', 'guard_name' => 'web']);

        $this->delete(route('permission-manager.roles.destroy', $role))
            ->assertRedirect(route('permission-manager.roles.index'));

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_sync_permissions_assigns_permissions_and_logs_assigned(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);

        $this->post(route('permission-manager.roles.sync-permissions', $role), [
            'permission_ids' => [$permission->id],
        ])->assertRedirect(route('permission-manager.roles.show', $role));

        $this->assertTrue($role->fresh()->hasPermissionTo('edit posts'));

        $this->assertDatabaseHas('permission_manager_audit_logs', [
            'action' => 'assigned',
            'entity_type' => 'role',
            'entity_id' => $role->id,
        ]);
    }

    public function test_sync_permissions_logs_revoked_when_permissions_are_removed(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $this->post(route('permission-manager.roles.sync-permissions', $role), [
            'permission_ids' => [],
        ])->assertRedirect();

        $this->assertFalse($role->fresh()->hasPermissionTo('edit posts'));

        $this->assertDatabaseHas('permission_manager_audit_logs', [
            'action' => 'revoked',
            'entity_type' => 'role',
            'entity_id' => $role->id,
        ]);
    }
}
