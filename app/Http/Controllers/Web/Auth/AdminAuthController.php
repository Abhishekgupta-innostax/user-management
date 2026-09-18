<?php

namespace App\Http\Controllers\Web\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterAdminRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.admin-register');
    }

    public function register(RegisterAdminRequest $request): RedirectResponse
    {
        $admin = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => Role::Admin,
        ]);

        Auth::login($admin);

        return redirect()->route('admin.dashboard');
    }

    public function showLogin(): View
    {
        return view('auth.admin-login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        if (! Auth::user()->isAdmin()) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'These credentials do not have administrator access.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
