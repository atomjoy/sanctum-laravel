<?php

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/test/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Login admin spa
Route::get('/login/admin', function () {
    $user = Admin::first();
    Auth::guard('admin')->login($user);

    return response()->json([
        'user' => $user
    ]);
});

Route::get('/login/user', function () {
    $user = User::first();
    Auth::guard('web')->login($user);

    return response()->json([
        'user' => $user
    ]);
});

// Route /api/user
// Route::middleware(['auth:web,sanctum'])
Route::middleware(['auth:web,sanctum', 'sanctum_web'])
    ->name('api.')
    ->group(function () {
        Route::get('/user', function (Request $request) {
            // Only user required without sanctum_web middleware
            // if (! Auth::user() instanceof User) {
            //     throw new \Exception("Forbidden.", 403);
            // }

            return response()->json([
                'message' => 'Authenticated.',
                'user' => Auth::user()->fresh(),
            ]);
        })->name('user');
    });

// Route /api/admin/user (you can use ->prefix('admin'))
// Route::middleware(['auth:admin,sanctum'])
Route::middleware(['auth:admin,sanctum', 'sanctum_admin'])
    ->name('api.admin.')
    ->group(function () {
        Route::get('/admin/user', function (Request $request) {
            // Only admin required without sanctum_admin middleware
            // if (! Auth::user() instanceof Admin) {
            //     throw new \Exception("Forbidden.", 403);
            // }

            return response()->json([
                'message' => 'Authenticated.',
                'user' => Auth::user()->fresh(),
            ]);
        })->name('admin.user');
    });


// Sanctum middleware ignoruje abilities i ability middleware dla zapytań SPA
// bez bearer token i loguje usera z sessją z guardem tablicy guards (trzeba sprawdzać instanceof dla usera).
// Nie używać multi guards i abilities w middleware wykluczaja się.
// Można usunąć abilities:admin-token middlewate i dodać $user->tokenCan('admin-token') tylko dla tokenów.
Route::middleware(['auth:sanctum', 'sanctum_admin', 'abilities:admin-token'])
    ->name('api.')
    ->group(function () {
        // Logged user
        Route::get('/admin/user/ability', function (Request $request) {
            // Only admin required without sanctum_admin middleware
            // if (! Auth::user() instanceof Admin) {
            //     return response()->json([
            //         'message' => 'Forbidden.',
            //         'user' => Auth::user()->fresh(),
            //     ], 403);
            // }

            return response()->json([
                'message' => 'Authenticated.',
                'user' => Auth::user()->fresh(),
            ]);
        })->name('admin.user.ability');
    });
