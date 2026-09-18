<?php

declare(strict_types=1);

namespace Shakeelnasafian\PermissionManager\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Shakeelnasafian\PermissionManager\Models\AuditLog;
use Shakeelnasafian\PermissionManager\Tests\TestCase;
use Spatie\Permission\Models\Permission;

class MigrationTest extends TestCase
{
    public function test_group_column_is_added_to_permissions_table(): void
    {
        $this->assertTrue(Schema::hasColumn('permissions', 'group'));
    }

    public function test_extra_columns_are_added_to_roles_table(): void
    {
        $this->assertTrue(Schema::hasColumn('roles', 'description'));
        $this->assertTrue(Schema::hasColumn('roles', 'hierarchy_level'));
        $this->assertTrue(Schema::hasColumn('roles', 'is_super_admin'));
    }

    public function test_audit_log_table_is_created(): void
    {
        $this->assertTrue(Schema::hasTable('permission_manager_audit_logs'));

        foreach (['action', 'entity_type', 'entity_id', 'old_values', 'new_values', 'ip_address', 'user_agent'] as $column) {
            $this->assertTrue(
                Schema::hasColumn('permission_manager_audit_logs', $column),
                "Expected audit log table to have column [{$column}]."
            );
        }
    }

    public function test_running_migrations_again_is_a_no_op(): void
    {
        // The guard clauses (hasColumn / hasTable returns) must make a second
        // pass idempotent rather than throwing "column already exists".
        $exitCode = $this->artisan('migrate')->run();

        $this->assertSame(0, $exitCode);
        $this->assertTrue(Schema::hasColumn('permissions', 'group'));
    }

    public function test_group_column_persists_a_value(): void
    {
        $permission = Permission::create(['name' => 'view reports', 'guard_name' => 'web']);
        $permission->group = 'Reports';
        $permission->save();

        $this->assertSame('Reports', $permission->fresh()->group);
    }

    public function test_audit_log_casts_value_columns_to_arrays(): void
    {
        $log = AuditLog::create([
            'action' => 'updated',
            'entity_type' => 'role',
            'entity_id' => 1,
            'entity_name' => 'editor',
            'old_values' => ['permissions' => ['a']],
            'new_values' => ['permissions' => ['a', 'b']],
            'created_at' => now(),
        ]);

        $fresh = $log->fresh();

        $this->assertSame(['permissions' => ['a']], $fresh->old_values);
        $this->assertSame(['permissions' => ['a', 'b']], $fresh->new_values);
    }
}
