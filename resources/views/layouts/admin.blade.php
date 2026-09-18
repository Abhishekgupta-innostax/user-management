@extends('layouts.app')

@section('content')
    <div class="d-flex">
        <aside class="admin-sidebar p-3" style="width: 240px; flex-shrink: 0;">
            <nav class="nav flex-column gap-1">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    Manage Users
                </a>
            </nav>
        </aside>

        <div class="flex-grow-1 p-4">
            @include('partials.flash-messages')

            @php $nonFieldErrors = collect($errors->default->messages())->except(['name', 'email', 'role'])->flatten(); @endphp
            @if ($nonFieldErrors->isNotEmpty())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($nonFieldErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('admin-content')
        </div>
    </div>
@endsection
