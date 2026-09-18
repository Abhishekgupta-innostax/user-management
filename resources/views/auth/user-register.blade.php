@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 fw-bold mb-4 text-center">Create Your Account</h1>

                        <form method="POST" action="{{ route('register.store') }}" novalidate>
                            @csrf

                            {{-- BUG U06 (intentional): error-field="email" makes the Full Name
                                 field display the Email field's validation error instead of
                                 its own. This prop should be removed. --}}
                            <x-input-group name="name" label="Full Name" required autofocus error-field="email" />
                            <x-input-group name="email" label="Email Address" type="email" required />
                            <x-input-group name="password" label="Password" type="password" required />
                            <x-input-group name="password_confirmation" label="Confirm Password" type="password" required />

                            {{-- BUG U03 (intentional): fixed height + overflow:hidden clips
                                 the bottom of the button; this wrapper should not exist. --}}
                            <div style="max-height: 24px; overflow: hidden;">
                                <button type="submit" class="btn btn-primary w-100">Register</button>
                            </div>
                        </form>

                        <p class="text-center mt-3 mb-0">
                            Already have an account? <a href="{{ route('login') }}">Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
