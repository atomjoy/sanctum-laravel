<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Sanctum SPA test.
     */
    public function test_spa_login_user(): void
    {
        $this->seed();

        $response = $this->getJson('/api/login/user');
        $response->assertStatus(200);

        $response = $this->getJson('/api/login/admin');
        $response->assertStatus(200);

        $response = $this->getJson('/api/user');
        $response->assertStatus(200)->assertJson(['user' => [
            'name' => 'Test User',
            'email' => 'user@github.com',
        ]]);

        $response = $this->getJson('/api/admin/user');
        $response->assertStatus(200)->assertJson(['user' => [
            'name' => 'Test Admin',
            'email' => 'admin@github.com',
        ]]);
    }

    /**
     * Sanctum API test User.
     */
    public function test_api_user_login(): void
    {
        $this->seed();

        $user = User::first();
        $token = $user->createToken('mobile-token-user', ['*'], now()->addYear())->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/user');
        $response->assertStatus(200)->assertJson(['user' => [
            'name' => 'Test User'
        ]]);
    }

    /**
     * Sanctum API test Admin.
     */
    public function test_api_admin_login(): void
    {
        $this->seed();

        $user = Admin::first();
        $token = $user->createToken('mobile-token-admin', ['*'], now()->addYear())->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/admin/user');
        $response->assertStatus(200)->assertJson(['user' => [
            'name' => 'Test Admin'
        ]]);
    }

    /**
     * A basic test example.
     */
    public function test_api_login_user_cant_get_admin(): void
    {
        $this->seed();

        $user = User::first();
        $token = $user->createToken('mobile-token-user', ['user-token'], now()->addYear())->plainTextToken;

        // Admin route
        $response = $this->withToken($token)->getJson('/api/admin/user');
        $response->assertStatus(403);
    }

    /**
     * A basic test example.
     */
    public function test_api_login_admin_cant_get_user(): void
    {
        $this->seed();

        $user = Admin::first();
        $token = $user->createToken('mobile-token-admin', ['admin-token'], now()->addYear())->plainTextToken;

        // User route
        $response = $this->withToken($token)->getJson('/api/user');
        $response->assertStatus(403);
    }

    /**
     * Ability test.
     */
    public function test_admin_login_token_ok(): void
    {
        $this->seed();

        $user = Admin::first();
        $token = $user->createToken('mobile-admin-token', ['admin-token'], now()->addYear())->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/admin/user/ability');
        $response->assertStatus(200)->assertJson(['user' => ['name' => 'Test Admin']]);
    }

    /**
     * Ability test error.
     */
    public function test_admin_login_token_err(): void
    {
        $this->seed();

        $user = Admin::first();
        $token = $user->createToken('mobile-user-token', ['user-token'], now()->addYear())->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/admin/user/ability');
        $response->assertStatus(403);
    }
}
