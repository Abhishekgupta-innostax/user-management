@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'errorBag' => 'default',
    'errorField' => null,
])

@php
    $bag = $errors->getBag($errorBag);
    // BUG U06 (intentional): errorField lets a field display another field's
    // validation error. It should not be used — each field should always check
    // its own $name (i.e. errorField should default to $name, not be overridable).
    $errorKey = $errorField ?? $name;
@endphp

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-control' . ($bag->has($errorKey) ? ' is-invalid' : '')]) }}
    >
    @if ($bag->has($errorKey))
        <div class="invalid-feedback">{{ $bag->first($errorKey) }}</div>
    @endif
</div>
