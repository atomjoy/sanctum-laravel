# Laravel Sanctum SPA Multi Guard Auth

SPA multi guard authentication with Sanctum and Laravel 12.

## Download Run & Test

```sh
composer update
php artisan test
```

## Tutorial create project

```sh
composer create-project laravel/laravel sanctum
```

## Sanctum

Laravel Sanctum docs https://laravel.com/docs/12.x/sanctum#spa-authentication

### Install

```sh
php artisan install:api
```

### User

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens,
}
```

### Middleware

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->statefulApi();
})
```

## Multi guards

### Add guard

config/auth.php

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
        //'session_guard' => 'web',
        //'cookie' => 'web_session',
    ],

    'admin' => [
        'driver' => 'session',
        'provider' => 'admins',
        //'session_guard' => 'admin',
        //'cookie' => 'admin_session',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => env('AUTH_MODEL', App\Models\User::class),
    ],
    'admins' => [
        'driver' => 'eloquent',
        'model' => env('AUTH_MODEL_ADMIN', App\Models\Admin::class),
    ],
],
```

### User model

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens;

    /**
	 * Default guard name
	 *
	 * @var string
	 */
	protected $guard = 'web';

    /**
	 * Default table name
	 *
	 * @var string
	 */
    protected $table = 'users';
}
```

### Admin model

Create Admin model with required migrations, factory (copy from user and change).

```php
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens;

    /**
	 * Default guard name
	 *
	 * @var string
	 */
	protected $guard = 'admin';

    /**
	 * Default table name
	 *
	 * @var string
	 */
    protected $table = 'admins';
}
```

### Web routes

routes/web.php

```php
// Guard web for User
Route::middleware(['auth:web'])->group(function () {

    Route::get('/user', function (Request $request) {

        return response()->json([
            'message' => 'Authenticated.',
            'user' => Auth::guard('web')->user()->fresh(),
        ]);
    })->name('user');
});


// Guard admin for Admin
Route::middleware(['auth:admin'])->group(function () {

    Route::get('/admin/user', function (Request $request) {

        return response()->json([
            'message' => 'Authenticated.',
            'user' => Auth::guard('admin')->user()->fresh(),
        ]);
    })->name('admin.user');
});
```

### Sanctum SPA and API routes

routes/api.php

```php
// Route /api/user
Route::middleware(['auth:web,sanctum', 'sanctum_web'])
    ->name('api.')
    ->group(function () {
    Route::get('/user', function (Request $request) {
            // Only user
            if (! Auth::user() instanceof User) {
                throw new \Exception("Forbidden.", 403);
            }

            return response()->json([
                'message' => 'Authenticated.',
                'user' => Auth::user()->fresh(),
            ]);
        })->name('user');
});

// Route /api/admin/user (you can use ->prefix('admin'))
Route::middleware(['auth:admin,sanctum', 'sanctum_admin'])
    ->name('api.admin.')
    ->group(function () {
    Route::get('/admin/user', function (Request $request) {
            // Only admin
            if (! Auth::user() instanceof Admin) {
                throw new \Exception("Forbidden.", 403);
            }

            return response()->json([
                'message' => 'Authenticated.',
                'user' => Auth::user()->fresh(),
            ]);
        })->name('admin.user');
});
```

### Update middlewares

```php
use App\Http\Middleware\Sanctum\SanctumAdmin;
use App\Http\Middleware\Sanctum\SanctumWeb;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

->withMiddleware(function (Middleware $middleware): void {
    // Sanctum SPA
    $middleware->statefulApi();
    // Import
    $middleware->alias([
        // Sanctum multi guards
        'sanctum_web' => SanctumWeb::class,
        'sanctum_admin' => SanctumAdmin::class,

        // Sanctum abilities
        // 'abilities' => CheckAbilities::class,
        // 'ability' => CheckForAnyAbility::class,
    ]);
    // Sanctum API
    $middleware->api(prepend: [
        \App\Http\Middleware\Sanctum\ExpiredToken::class
    ]);
})
```

### Sanctum Admin middleware

```php
<?php

namespace App\Http\Middleware\Sanctum;

use Closure;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sanctum logged admin middleware.
 */
class SanctumAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::user() instanceof Admin) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}
```

### Sanctum Web middleware

```php
<?php

namespace App\Http\Middleware\Sanctum;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sanctum logged web middleware.
 */
class SanctumWeb
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::user() instanceof User) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}
```

### Sanctum token middleware

```php
<?php

namespace App\Http\Middleware\Sanctum;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Sanctum expired token middleware.
 *
 * Add middleware in bootstrap/app.php
 * $middleware->api(prepend: [ \App\Http\Middleware\Sanctum\ExpiredToken::class ]);
 */
class ExpiredToken
{
    public function handle(Request $request, Closure $next)
    {
        $bearer = $request->bearerToken();

        if ($bearer) {
            $token = PersonalAccessToken::findToken($bearer);

            if ($token instanceof PersonalAccessToken) {
                if($token->expires_at && $token->expires_at->isPast()) {
                    return response()->json([
                        'message' => 'Expired Token.',
                        'token_expired' => $token->expires_at && $token->expires_at->isPast(),
                        'token_details' => $token
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
```
