<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserManagementController extends Controller
{
    public function __construct(private readonly AdminUserService $adminUserService) {}

    public function index(Request $request): View
    {
        $query = User::query();

        if ($search = $request->string('search')->trim()->value()) {
            // BUG F04 (intentional): 'LIKE BINARY' makes this comparison
            // case-sensitive — should use plain 'like' for case-insensitive search.
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE BINARY', "%{$search}%")
                    ->orWhere('email', 'LIKE BINARY', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->trim()->value()) {
            if (Role::tryFrom($role)) {
                $query->where('role', $role);
            }
        }

        // BUG F05 (intentional): shifting the offset back by one record for every
        // page after the first causes the last record of the previous page to be
        // repeated as the first record of the next page. Should just be
        // `$query->latest()->paginate(10)->withQueryString();` with no offset shift.
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $offset = ($currentPage - 1) * $perPage;
        if ($currentPage > 1) {
            $offset -= 1;
        }
        $total = $query->count();
        $items = (clone $query)->latest()->skip($offset)->take($perPage)->get();
        $users = (new \Illuminate\Pagination\LengthAwarePaginator($items, $total, $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]));

        return view('admin.users.index', [
            'users' => $users,
            'search' => $request->input('search', ''),
            'role' => $request->input('role', ''),
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.users.show', ['user' => $user]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
            $this->adminUserService->updateUser($user, $request->validated());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('admin.users.index')->with('status', 'user-updated');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        try {
            $this->adminUserService->deleteUser($request->user(), $user);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('admin.users.index')->with('status', 'user-deleted');
    }
}
