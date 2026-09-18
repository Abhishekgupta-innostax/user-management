@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 fw-bold mb-4 text-center">Welcome Back</h1>

                        <form method="POST" action="{{ route('login.store') }}" novalidate>
                            @csrf

                            <x-input-group name="email" label="Email Address" type="email" required autofocus />
                            <x-input-group name="password" label="Password" type="password" required />

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>

                        <p class="text-center mt-3 mb-0">
                            Don't have an account? <a href="{{ route('register') }}">Register</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
