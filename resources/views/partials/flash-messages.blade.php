@php
    $statusMessages = [
        'profile-updated' => 'Your profile was updated successfully.',
        'password-updated' => 'Your password was changed successfully.',
        'account-deleted' => 'Your account has been deleted.',
        'user-updated' => 'User updated successfully.',
        'user-deleted' => 'User deleted successfully.',
    ];
@endphp

@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $statusMessages[session('status')] ?? session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
