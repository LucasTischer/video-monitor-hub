@props(['status'])

<x-flash-message :message="$status" variant="inline" {{ $attributes }} />
