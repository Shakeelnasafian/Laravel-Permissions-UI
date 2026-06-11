<?php

declare(strict_types=1);

namespace Shakeelnasafian\PermissionManager\Tests\Unit;

use Shakeelnasafian\PermissionManager\Models\AuditLog;
use Shakeelnasafian\PermissionManager\Services\AuditService;
use Shakeelnasafian\PermissionManager\Tests\TestCase;

class AuditServiceTest extends TestCase
{
    public function test_it_writes_an_audit_record(): void
    {
        app(AuditService::class)->log(
            'assigned',
            'role',
            7,
            'editor',
            ['permissions' => []],
            ['permissions' => ['edit posts']]
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame('assigned', $log->action);
        $this->assertSame('role', $log->entity_type);
        $this->assertSame(7, (int) $log->entity_id);
        $this->assertSame('editor', $log->entity_name);
        $this->assertSame(['permissions' => ['edit posts']], $log->new_values);
    }

    public function test_it_captures_the_authenticated_user_id(): void
    {
        $user = $this->actingAsUser();

        app(AuditService::class)->log('created', 'permission', 1, 'view');

        $this->assertSame($user->id, (int) AuditLog::query()->latest('id')->first()->user_id);
    }

    public function test_it_does_nothing_when_audit_logging_is_disabled(): void
    {
        config(['permission-manager.enable_audit_log' => false]);

        app(AuditService::class)->log('created', 'role', 1, 'admin');

        $this->assertSame(0, AuditLog::query()->count());
    }

    public function test_it_stores_a_null_user_id_for_unauthenticated_actions(): void
    {
        app(AuditService::class)->log('created', 'role', 1, 'admin');

        $this->assertNull(AuditLog::query()->latest('id')->first()->user_id);
    }
}
