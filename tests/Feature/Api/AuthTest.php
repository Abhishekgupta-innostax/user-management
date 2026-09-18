<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_via_api(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'API Jane',
            'email' => 'apijane@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'apijane@example.com')
            ->assertJsonPath('data.user.role', 'user')
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'apijane@example.com']);
    }

    public function test_api_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Dup',
            'email' => 'dup@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_api_registration_requires_all_fields(): void
    {
        $this->postJson('/api/v1/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login_via_api_and_receive_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertOk()->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_api_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_can_fetch_me(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_unauthenticated_request_to_me_is_rejected(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_admin_registration_endpoint_does_not_exist(): void
    {
        $this->postJson('/api/v1/admin/register', [
            'name' => 'API Admin',
            'email' => 'apiadmin@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertNotFound();
    }

    public function test_admin_can_login_via_api(): void
    {
        $admin = User::factory()->admin()->create(['password' => Hash::make('Password123!')]);

        $this->postJson('/api/v1/admin/login', [
            'email' => $admin->email,
            'password' => 'Password123!',
        ])->assertOk()->assertJsonPath('data.user.role', 'admin');
    }

    public function test_regular_user_cannot_login_via_admin_api_endpoint(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $this->postJson('/api/v1/admin/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertForbidden();
    }
}
