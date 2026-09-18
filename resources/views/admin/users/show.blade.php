@extends('layouts.admin')

@section('title', 'View User')

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0">User Details</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back to Users</a>
    </div>

    <div class="card card-stat" style="max-width: 500px;">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4">ID</dt>
                <dd class="col-sm-8">{{ $user->id }}</dd>

                <dt class="col-sm-4">Name</dt>
                <dd class="col-sm-8">{{ $user->name }}</dd>

                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8">{{ $user->email }}</dd>

                <dt class="col-sm-4">Role</dt>
                <dd class="col-sm-8">
                    <span class="badge {{ $user->isAdmin() ? 'bg-dark' : 'bg-primary' }}">{{ $user->role->label() }}</span>
                </dd>

                <dt class="col-sm-4">Registered</dt>
                <dd class="col-sm-8">{{ $user->created_at->format('F j, Y g:i A') }}</dd>

                <dt class="col-sm-4">Last Updated</dt>
                <dd class="col-sm-8">{{ $user->updated_at->format('F j, Y g:i A') }}</dd>
            </dl>
        </div>
        <div class="card-footer bg-white">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">Edit User</a>
        </div>
    </div>
@endsection
