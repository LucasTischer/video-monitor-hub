@props([
    'message' => null,
    'type' => 'success',
    'duration' => 4000,
    'variant' => 'banner',
])

@php
    $bannerClasses = match ($type) {
        'error' => 'rounded-md border border-red-900 bg-red-950 p-4 text-sm font-medium text-red-100',
        default => 'rounded-md border border-green-900 bg-green-950 p-4 text-sm font-medium text-green-100',
    };

    $inlineClasses = match ($type) {
        'error' => 'text-sm font-medium text-red-400',
        default => 'text-sm font-medium text-green-400',
    };

    $classes = $variant === 'inline' ? $inlineClasses : $bannerClasses;
@endphp

@if ($message)
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition.opacity.duration.300ms
        x-init="setTimeout(() => show = false, {{ (int) $duration }})"
        role="{{ $type === 'error' ? 'alert' : 'status' }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $message }}
    </div>
@endif
