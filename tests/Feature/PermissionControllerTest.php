<?php

declare(strict_types=1);

namespace Shakeelnasafian\PermissionManager\Tests\Feature;

use Shakeelnasafian\PermissionManager\Tests\TestCase;
use Spatie\Permission\Models\Permission;

class PermissionControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsUser();
    }

    public function test_index_loads(): void
    {
        Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);

        $this->get(route('permission-manager.permissions.index'))
            ->assertOk()
            ->assertSee('edit posts');
    }

    public function test_store_creates_a_permission_with_a_group(): void
    {
        $response = $this->post(route('permission-manager.permissions.store'), [
            'name' => 'edit articles',
            'group' => 'Articles',
        ]);

        $response->assertRedirect(route('permission-manager.permissions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('permissions', [
            'name' => 'edit articles',
            'guard_name' => 'web',
            'group' => 'Articles',
        ]);
    }

    public function test_store_requires_a_name(): void
    {
        $this->from(route('permission-manager.permissions.create'))
            ->post(route('permission-manager.permissions.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_store_rejects_a_duplicate_name_in_the_same_guard(): void
    {
        Permission::create(['name' => 'edit articles', 'guard_name' => 'web']);

        $this->from(route('permission-manager.permissions.create'))
            ->post(route('permission-manager.permissions.store'), ['name' => 'edit articles'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Permission::query()->where('name', 'edit articles')->count());
    }

    public function test_update_changes_the_name_and_group(): void
    {
        $permission = Permission::create(['name' => 'old name', 'guard_name' => 'web']);

        $this->put(route('permission-manager.permissions.update', $permission), [
            'name' => 'new name',
            'group' => 'Content',
        ])->assertRedirect(route('permission-manager.permissions.index'));

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'new name',
            'group' => 'Content',
        ]);
    }

    public function test_destroy_deletes_the_permission(): void
    {
        $permission = Permission::create(['name' => 'removable', 'guard_name' => 'web']);

        $this->delete(route('permission-manager.permissions.destroy', $permission))
            ->assertRedirect(route('permission-manager.permissions.index'));

        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    public function test_index_filters_by_search_term(): void
    {
        Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete posts', 'guard_name' => 'web']);

        $this->get(route('permission-manager.permissions.index', ['search' => 'edit']))
            ->assertOk()
            ->assertSee('edit posts')
            ->assertDontSee('delete posts');
    }

    public function test_index_filters_by_group(): void
    {
        Permission::create(['name' => 'edit posts', 'guard_name' => 'web', 'group' => 'Posts']);
        Permission::create(['name' => 'edit users', 'guard_name' => 'web', 'group' => 'Users']);

        $this->get(route('permission-manager.permissions.index', ['group' => 'Posts']))
            ->assertOk()
            ->assertSee('edit posts')
            ->assertDontSee('edit users');
    }
}
