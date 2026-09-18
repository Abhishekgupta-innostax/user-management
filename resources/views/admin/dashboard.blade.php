@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('admin-content')
    <h1 class="h3 fw-bold mb-4">Admin Dashboard</h1>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-stat">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Users</p>
                    <h2 class="fw-bold">{{ $totalUsers }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Administrators</p>
                    <h2 class="fw-bold">{{ $totalAdmins }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Accounts</p>
                    <h2 class="fw-bold">{{ $totalUsers + $totalAdmins }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-stat">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 fw-bold mb-0">Recent Registrations</h2>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">View All Users</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentUsers as $recentUser)
                            <tr>
                                <td>{{ $recentUser->name }}</td>
                                <td>{{ $recentUser->email }}</td>
                                <td>
                                    <span class="badge {{ $recentUser->isAdmin() ? 'bg-dark' : 'bg-primary' }}">
                                        {{ $recentUser->role->label() }}
                                    </span>
                                </td>
                                <td>{{ $recentUser->created_at->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No users registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
