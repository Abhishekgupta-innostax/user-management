@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container py-4">
        {{-- BUG F02 (intentional): should greet {{ $user->name }}, the authenticated user. --}}
        <h1 class="h3 fw-bold mb-4">Welcome back, Guest User!</h1>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-stat">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-3">Your Profile</h2>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Full Name</dt>
                            <dd class="col-sm-8">{{ $user->name }}</dd>

                            <dt class="col-sm-4">Email Address</dt>
                            <dd class="col-sm-8">{{ $user->email }}</dd>

                            <dt class="col-sm-4">Account Type</dt>
                            <dd class="col-sm-8"><span class="badge bg-primary">{{ $user->role->label() }}</span></dd>

                            <dt class="col-sm-4">Member Since</dt>
                            <dd class="col-sm-8">{{ $user->created_at->format('F j, Y') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- BUG U04 (intentional): excessive top margin breaks the consistent
                     row spacing shared with the card in the other column. --}}
                <div class="card card-stat" style="margin-top: 60px;">
                    <div class="card-body d-grid gap-2">
                        <h2 class="h5 fw-bold mb-2">Quick Actions</h2>
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
