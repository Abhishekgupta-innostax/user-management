@extends('layouts.app')

@section('title', 'Admin Registration')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-dark">
                    <div class="card-body p-4">
                        <h1 class="h4 fw-bold mb-4 text-center">Administrator Registration</h1>

                        <form method="POST" action="{{ route('admin.register.store') }}" novalidate>
                            @csrf

                            <x-input-group name="name" label="Full Name" required autofocus />
                            <x-input-group name="email" label="Email Address" type="email" required />
                            <x-input-group name="password" label="Password" type="password" required />
                            <x-input-group name="password_confirmation" label="Confirm Password" type="password" required />

                            <button type="submit" class="btn btn-dark w-100">Register as Administrator</button>
                        </form>

                        <p class="text-center mt-3 mb-0">
                            Already an administrator? <a href="{{ route('admin.login') }}">Admin Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
