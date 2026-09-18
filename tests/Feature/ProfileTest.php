<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee($user->email);
    }

    public function test_guest_cannot_view_profile_page(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_user_can_update_own_name_and_email(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect();
        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame('updated@example.com', $user->fresh()->email);
    }

    public function test_user_cannot_change_own_role_via_profile_update(): void
    {
        $user = User::factory()->create(['role' => Role::User]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'admin',
        ]);

        $this->assertTrue($user->fresh()->role === Role::User);
    }

    public function test_profile_update_requires_unique_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_password_change_fails_with_incorrect_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'WrongPassword!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }

    public function test_user_can_delete_own_account_with_correct_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $response = $this->actingAs($user)->delete(route('profile.destroy'), [
            'current_password' => 'Password123!',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_account_deletion_fails_with_incorrect_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $response = $this->actingAs($user)->delete(route('profile.destroy'), [
            'current_password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('current_password', null, 'deleteAccount');
        $this->assertNotNull($user->fresh());
    }

    public function test_user_cannot_edit_another_users_profile_directly(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['name' => 'Original Name']);

        // Regular users have no route to target another user's profile by id;
        // profile routes always operate on the authenticated user only.
        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Hijacked Name',
            'email' => $user->email,
        ]);

        $this->assertSame('Original Name', $other->fresh()->name);
    }
}
