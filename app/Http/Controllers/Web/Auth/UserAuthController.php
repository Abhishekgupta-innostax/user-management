<?php

namespace App\Http\Controllers\Web\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.user-register');
    }

    public function register(RegisterUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => Role::User,
        ]);

        // BUG F01 (intentional): should call Auth::login($user) here and
        // redirect to 'dashboard'. Skipping the login means the redirect to
        // 'login' actually lands on the login form (a logged-in session
        // would otherwise be bounced back to /dashboard by the 'guest'
        // middleware, masking the bug).
        return redirect()->route('login');
    }

    public function showLogin(): View
    {
        return view('auth.user-login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        return $user->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->intended(route('dashboard'));
    }
}
