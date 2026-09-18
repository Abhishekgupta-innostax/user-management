@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Simple, Secure User Management</h1>
            <p class="lead mb-4">
                Register, manage profiles, and administer users with a clean, role-based
                dashboard built on Laravel &mdash; complete with a REST API secured by Sanctum.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                @guest
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg px-4">Get Started</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">Sign In</a>
                @else
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                        class="btn btn-light btn-lg px-4">Go to Dashboard</a>
                @endguest
            </div>
        </div>
    </section>

    <section class="container py-5">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 h-100">
                    <h3 class="h5 fw-bold">Role-Based Access</h3>
                    <p class="text-muted">Separate, secure flows for regular users and administrators, enforced by middleware and policies.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 h-100">
                    <h3 class="h5 fw-bold">Full Profile Control</h3>
                    <p class="text-muted">Users can view, edit, and delete their own accounts, with secure password confirmation.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 h-100">
                    <h3 class="h5 fw-bold">REST API Included</h3>
                    <p class="text-muted">Every feature is also available as a versioned, token-authenticated JSON API.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
