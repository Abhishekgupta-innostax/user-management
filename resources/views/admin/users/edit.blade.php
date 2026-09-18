@extends('layouts.admin')

@section('title', 'Edit User')

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0">Edit User</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back to Users</a>
    </div>

    <div class="card card-stat" style="max-width: 500px;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" novalidate>
                @csrf
                @method('PUT')

                <x-input-group name="name" label="Full Name" :value="$user->name" required autofocus />
                <x-input-group name="email" label="Email Address" type="email" :value="$user->email" required />

                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-select {{ $errors->has('role') ? 'is-invalid' : '' }}">
                        <option value="user" {{ old('role', $user->role->value) === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('role', $user->role->value) === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
@endsection
