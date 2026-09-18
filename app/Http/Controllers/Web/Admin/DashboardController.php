<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::where('role', Role::User)->count(),
            'totalAdmins' => User::where('role', Role::Admin)->count(),
            'recentUsers' => User::latest()->take(5)->get(),
        ]);
    }
}
