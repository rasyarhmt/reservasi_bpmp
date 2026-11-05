@props(['reservation'])

@php
    $variants = [
        'booked' => 'secondary',
        'checked_in' => 'success',
        'checked_out' => 'outline',
        'cancelled' => 'destructive',
    ];
@endphp

<x-badge variant="{{ $variants[$reservation->status] ?? 'secondary' }}">
    {{ $reservation->statusLabel() }}
</x-badge>
