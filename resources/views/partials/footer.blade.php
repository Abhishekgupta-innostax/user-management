<footer>
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <p class="mb-2 mb-md-0">&copy; {{ date('Y') }} User Management. All rights reserved.</p>
        <div class="d-flex gap-3">
            <a href="{{ route('home') }}" class="text-decoration-none">Home</a>
            <a href="{{ route('login') }}" class="text-decoration-none">Login</a>
            <a href="{{ route('register') }}" class="text-decoration-none">Register</a>
        </div>
    </div>
</footer>
