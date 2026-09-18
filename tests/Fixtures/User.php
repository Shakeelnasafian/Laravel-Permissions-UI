<?php

declare(strict_types=1);

namespace Shakeelnasafian\PermissionManager\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}
