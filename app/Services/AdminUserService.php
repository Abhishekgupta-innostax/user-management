<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminUserService
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => Role::User,
        ]);
    }

    /**
     * @param  array{name: string, email: string, role: Role|string}  $data
     *
     * @throws ValidationException
     */
    public function updateUser(User $target, array $data): User
    {
        $newRole = $data['role'] instanceof Role ? $data['role'] : Role::from($data['role']);

        if ($target->role === Role::Admin && $newRole === Role::User && $this->isLastAdmin($target)) {
            throw ValidationException::withMessages([
                'role' => 'You cannot demote the last remaining administrator.',
            ]);
        }

        $target->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $newRole,
        ]);

        return $target;
    }

    /**
     * @throws ValidationException
     */
    public function deleteUser(User $actingAdmin, User $target): void
    {
        if ($actingAdmin->id === $target->id) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete your own administrator account.',
            ]);
        }

        if ($target->role === Role::Admin && $this->isLastAdmin($target)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete the last remaining administrator.',
            ]);
        }

        $target->delete();
    }

    private function isLastAdmin(User $target): bool
    {
        return User::where('role', Role::Admin)
            ->where('id', '!=', $target->id)
            ->doesntExist();
    }
}
