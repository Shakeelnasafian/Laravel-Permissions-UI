<?php

declare(strict_types=1);

namespace Shakeelnasafian\PermissionManager\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Shakeelnasafian\PermissionManager\Http\Middleware\AuthorizePermissionManager;
use Shakeelnasafian\PermissionManager\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthorizeMiddlewareTest extends TestCase
{
    public function test_it_aborts_with_403_for_a_guest(): void
    {
        $middleware = new AuthorizePermissionManager();

        try {
            $middleware->handle(Request::create('/permission-manager'), fn () => response('ok'));
            $this->fail('Expected a 403 HttpException for an unauthenticated request.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_it_allows_an_authenticated_request_when_no_gate_is_configured(): void
    {
        $this->actingAsUser();

        $middleware = new AuthorizePermissionManager();
        $response = $middleware->handle(Request::create('/permission-manager'), fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    public function test_it_denies_when_the_configured_gate_fails(): void
    {
        Gate::define('manage-permission-manager', fn () => false);
        config(['permission-manager.access_gate' => 'manage-permission-manager']);

        $this->actingAsUser();

        $this->get(route('permission-manager.permissions.index'))->assertForbidden();
    }

    public function test_it_allows_when_the_configured_gate_passes(): void
    {
        Gate::define('manage-permission-manager', fn () => true);
        config(['permission-manager.access_gate' => 'manage-permission-manager']);

        $this->actingAsUser();

        $this->get(route('permission-manager.permissions.index'))->assertOk();
    }
}
