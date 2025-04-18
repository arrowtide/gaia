@props([
    'level' => null,
    'size' => null,
    'class' => '',
])

@php
    $sizes = [
        1 => 'h1',
        2 => 'h2',
        3 => 'h3',
        4 => 'h4',
        5 => 'h5',
        6 => 'h6',
    ];

    $size = $size ? $size : $level;
@endphp

<h{{ $level }} class="{{ $sizes[$size] }} {{ $class }}" {{ $attributes}}>
    {{ $slot }}
</h{{ $level }}>
