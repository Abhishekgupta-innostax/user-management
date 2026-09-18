<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(private readonly AdminUserService $adminUserService) {}

    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->trim()->value()) {
            if (Role::tryFrom($role)) {
                $query->where('role', $role);
            }
        }

        $users = $query->latest()->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->adminUserService->createUser($request->validated());

        return response()->json([
            'message' => 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $this->adminUserService->updateUser($user, $request->validated());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Unable to update user.',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        try {
            $this->adminUserService->deleteUser($request->user(), $user);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Unable to delete user.',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
