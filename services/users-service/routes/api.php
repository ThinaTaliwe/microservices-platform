<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

Route::get('/health', function () {
    return response()->json(['service' => 'users-service', 'status' => 'ok']);
});

Route::get('/users', function () {
    return response()->json([
        ['id' => 1, 'name' => 'Test User'],
        ['id' => 2, 'name' => 'Admin User'],
    ]);
});

Route::get('/demo/users', function () {
    return DB::table('demo_users')->orderByDesc('id')->limit(20)->get();
});

Route::post('/demo/users', function (Request $request) {
    $name = $request->input('name', 'Demo User');
    $email = $request->input('email', 'demo'.rand(1000,9999).'@example.com');

    $id = DB::table('demo_users')->insertGetId([
        'name' => $name,
        'email' => $email,
        'created_at' => now(),
    ]);

    return ['ok' => true, 'id' => $id, 'name' => $name, 'email' => $email];
});
