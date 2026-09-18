<?php

namespace Tests\Feature\Api;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson('/api/v1/profile', [
                'name' => 'New Name',
                'email' => $user->email,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_user_cannot_escalate_role_via_api_profile_update(): void
    {
        $user = User::factory()->create(['role' => Role::User]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson('/api/v1/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'admin',
            ])
            ->assertOk();

        $this->assertTrue($user->fresh()->role === Role::User);
    }

    public function test_user_can_change_password_via_api(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_password_change_via_api_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'WrongPassword!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');
    }

    public function test_user_can_delete_own_account_via_api(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->deleteJson('/api/v1/profile', ['current_password' => 'Password123!'])
            ->assertOk();

        $this->assertNull($user->fresh());
    }

    public function test_account_deletion_via_api_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($user))
            ->deleteJson('/api/v1/profile', ['current_password' => 'WrongPassword!'])
            ->assertUnprocessable();

        $this->assertNotNull($user->fresh());
    }
}
