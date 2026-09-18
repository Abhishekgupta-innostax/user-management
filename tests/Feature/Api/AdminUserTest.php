<?php

namespace Tests\Feature\Api;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_regular_user_cannot_access_admin_user_list(): void
    {
        $user = User::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();
    }

    public function test_unauthenticated_request_to_admin_users_is_rejected(): void
    {
        $this->getJson('/api/v1/admin/users')->assertUnauthorized();
    }

    public function test_admin_can_list_users_with_pagination(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(20)->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->getJson('/api/v1/admin/users?per_page=10');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'per_page', 'total']])
            ->assertJsonCount(10, 'data');
    }

    public function test_admin_can_create_a_regular_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/admin/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $response->assertCreated()->assertJsonPath('data.role', 'user');
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com', 'role' => 'user']);
    }

    public function test_admin_created_user_is_always_a_regular_user_even_if_role_is_passed(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/admin/users', [
                'name' => 'New User',
                'email' => 'sneakyadmin@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'admin',
            ]);

        $response->assertCreated()->assertJsonPath('data.role', 'user');
    }

    public function test_admin_can_view_a_user_by_id(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->getJson("/api/v1/admin/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_viewing_nonexistent_user_returns_404(): void
    {
        $admin = User::factory()->admin()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->getJson('/api/v1/admin/users/999999')
            ->assertNotFound();
    }

    public function test_admin_can_update_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'name' => 'Updated By Admin',
                'email' => $user->email,
                'role' => 'user',
            ]);

        $response->assertOk()->assertJsonPath('data.name', 'Updated By Admin');
    }

    public function test_admin_can_delete_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->deleteJson("/api/v1/admin/users/{$user->id}")
            ->assertOk();

        $this->assertNull($user->fresh());
    }

    public function test_admin_cannot_delete_own_account_via_api(): void
    {
        $admin = User::factory()->admin()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->deleteJson("/api/v1/admin/users/{$admin->id}")
            ->assertUnprocessable();

        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_cannot_demote_the_last_admin_via_api(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->putJson("/api/v1/admin/users/{$admin->id}", [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'user',
            ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->assertTrue($admin->fresh()->role === Role::Admin);
    }

    public function test_admin_update_requires_unique_email(): void
    {
        $admin = User::factory()->admin()->create();
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->putJson("/api/v1/admin/users/{$userA->id}", [
                'name' => $userA->name,
                'email' => $userB->email,
                'role' => 'user',
            ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }
}
