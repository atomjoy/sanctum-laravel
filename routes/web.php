<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('welcome');
})->name('login');

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
