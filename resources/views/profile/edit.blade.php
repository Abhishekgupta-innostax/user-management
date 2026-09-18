@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
    <div class="container py-4">
        <h1 class="h3 fw-bold mb-4">Edit Profile</h1>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card card-stat">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-3">Profile Information</h2>
                        <form method="POST" action="{{ route('profile.update') }}" novalidate>
                            @csrf
                            @method('PUT')

                            <x-input-group name="name" label="Full Name" :value="$user->name" required autofocus />
                            <x-input-group name="email" label="Email Address" type="email" :value="$user->email" required />

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-stat mb-4">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-3">Change Password</h2>
                        <form method="POST" action="{{ route('profile.password') }}" novalidate>
                            @csrf
                            @method('PUT')

                            <x-input-group name="current_password" label="Current Password" type="password" required />
                            <x-input-group name="password" label="New Password" type="password" required />
                            <x-input-group name="password_confirmation" label="Confirm New Password" type="password" required />

                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>

                <div class="card card-stat border-danger">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-3 text-danger">Delete Account</h2>
                        <p class="text-muted small">This action is permanent and cannot be undone.</p>

                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            Delete My Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteAccountModalLabel">Confirm Account Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Please enter your current password to permanently delete your account.</p>
                        <x-input-group name="current_password" label="Current Password" type="password" required error-bag="deleteAccount" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->getBag('deleteAccount')->any())
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
                });
            </script>
        @endpush
    @endif
@endsection
