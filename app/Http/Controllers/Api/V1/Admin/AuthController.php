<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterAdminRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterAdminRequest $request): JsonResponse
    {
        $admin = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => Role::Admin,
        ]);

        $token = $admin->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Administrator registration successful.',
            'data' => [
                'user' => new UserResource($admin),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $admin = User::where('email', $request->validated('email'))->first();

        if (! $admin || ! Hash::check($request->validated('password'), $admin->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        if (! $admin->isAdmin()) {
            return response()->json([
                'message' => 'These credentials do not have administrator access.',
            ], 403);
        }

        $token = $admin->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($admin),
                'token' => $token,
            ],
        ]);
    }
}
