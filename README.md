# Laravel Spatie Permission Manager

[![tests](https://github.com/Shakeelnasafian/laravel-permissions-ui/actions/workflows/tests.yml/badge.svg)](https://github.com/Shakeelnasafian/laravel-permissions-ui/actions/workflows/tests.yml)

Elegant admin UI for managing roles, permissions, and assignments on top of `spatie/laravel-permission` - no Node build required.

## Features
- Blade + Alpine + Tailwind (CDN) UI with a standalone layout
- Role and permission CRUD with grouping support
- User role and direct permission assignment
- Audit log tracking for changes and sync operations
- Import/Export (JSON + CSV)

## Requirements
- PHP ^8.1
- Laravel ^10 || ^11 || ^12
- `spatie/laravel-permission` ^6

## Installation

```bash
composer require shakeelnasafian/laravel-spatie-permission-manager

php artisan vendor:publish --tag=permission-manager-config
php artisan vendor:publish --tag=permission-manager-migrations
php artisan migrate

# Optional: publish views to customize
php artisan vendor:publish --tag=permission-manager-views
```

Visit `/permission-manager` in the browser.

## Local Development (Path Repository)

If you want to install this package from a local folder into a Laravel app:

1. Add a path repository to the consuming app `composer.json` (adjust the path to where this repo lives).

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../laravel-permissions-ui",
      "options": { "symlink": true }
    }
  ]
}
```

2. Require the package from the Laravel app.

```bash
composer require shakeelnasafian/laravel-spatie-permission-manager:^0.1
```

## Configuration

After publishing, edit `config/permission-manager.php`:

- `route_prefix`: URL prefix for the UI
- `route_middleware`: middleware stack for all routes
- `access_gate`: optional Gate ability name to authorize access
- `guard`: default guard name
- `user_model`: user model class
- `super_admin_role`: role name shown as super admin
- `enable_audit_log`: toggle audit logging
- `enable_groups`: toggle permission groups
- `pagination`: pagination size
- `user_display_field`: user column shown in UI
- `user_search_fields`: searchable user fields

## Access Gate Example

```php
// App\Providers\AppServiceProvider.php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::before(function ($user, $ability) {
        return $user->hasRole(config('permission-manager.super_admin_role')) ? true : null;
    });
}
```

Set in config:

```php
'access_gate' => 'view-permission-manager',
```

## Import / Export Format

### JSON

```json
{
  "roles": [
    { "name": "editor", "guard_name": "web", "hierarchy_level": 10, "is_super_admin": false,
      "permissions": ["edit-posts", "view-posts"] }
  ],
  "permissions": [
    { "name": "edit-posts", "guard_name": "web", "group": "Posts" }
  ]
}
```

### CSV

One file, mixed rows:

```
type,name,guard_name,group,description,hierarchy_level,is_super_admin,permissions
permission,edit-posts,web,Posts,,,,
role,editor,web,,Content editor,10,0,edit-posts|view-posts
```

## Routes

All routes are registered under the configured prefix. For a full list:

```bash
php artisan route:list --path=permission-manager
```

## Testing

The package ships with an [Orchestra Testbench](https://github.com/orchestral/testbench) + PHPUnit suite covering the controllers, audit logging, the authorization middleware, and the migration guards.

```bash
composer install
composer test
```

CI runs the suite on every push and pull request across PHP 8.1–8.3 and Laravel 10/11/12.

## License

MIT. See `LICENSE` for details.
