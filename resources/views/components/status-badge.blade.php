@props(['status'])

@php
    $colors = [
        'pending' => 'warning text-dark',
        'confirmed' => 'info text-dark',
        'completed' => 'success',
        'cancelled' => 'danger',
        'active' => 'success',
        'inactive' => 'secondary',
    ];
@endphp

<span class="badge bg-{{ $colors[$status] ?? 'secondary' }}">{{ ucfirst($status) }}</span>