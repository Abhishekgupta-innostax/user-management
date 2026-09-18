<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_regular_user_cannot_access_admin_user_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_dashboard_with_stats(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('3')
            ->assertSee('1');
    }

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Findable User']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Findable User');
    }

    public function test_admin_can_search_users_by_name_or_email(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Alice Wonderland', 'email' => 'alice@example.com']);
        User::factory()->create(['name' => 'Bob Builder', 'email' => 'bob@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'alice']));

        $response->assertOk()->assertSee('Alice Wonderland')->assertDontSee('Bob Builder');
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = User::factory()->admin()->create();
        $plainUser = User::factory()->create(['name' => 'Plain Person']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'admin']));

        $response->assertOk()->assertDontSee('Plain Person');
    }

    public function test_admin_user_list_is_paginated(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $this->assertTrue($response->viewData('users')->hasPages());
    }

    public function test_admin_can_view_a_single_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee($user->email);
    }

    public function test_admin_can_update_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Renamed User',
            'email' => $user->email,
            'role' => 'user',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSame('Renamed User', $user->fresh()->name);
    }

    public function test_admin_can_promote_a_user_to_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'admin',
        ]);

        $this->assertTrue($user->fresh()->role === Role::Admin);
    }

    public function test_admin_cannot_demote_the_last_remaining_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->role === Role::Admin);
    }

    public function test_admin_can_delete_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertNull($user->fresh());
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));

        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_cannot_delete_the_last_remaining_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        // Deleting otherAdmin is fine since $admin remains.
        $this->actingAs($admin)->delete(route('admin.users.destroy', $otherAdmin));
        $this->assertNull($otherAdmin->fresh());

        // Now $admin is the last admin; attempting to delete self is blocked
        // by the self-delete rule (which also prevents ever reaching zero admins).
        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));
        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_users_index_handles_no_results_gracefully(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'nonexistent-person']));

        $response->assertOk()->assertSee('No users found');
    }
}
