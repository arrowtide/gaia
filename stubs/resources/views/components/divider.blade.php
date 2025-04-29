@props([
    'space' => '',
    'class' => '',
])

@php
    $spaceClasses = [
        'sm' => 'my-2',
        'md' => 'my-4',
        'lg' => 'my-6',
        'xl' => 'my-12',
    ];
    
    $classes = [
        'margin' => $spaceClasses[$space] ?? $space,
        'border' => "w-full h-0 border-t border-slate-800/10",
    ]
@endphp

@if ($slot->isEmpty())
    <div class="{{ $classes['border'] }} {{ $classes['margin'] }} {{ $class }}" role="none"></div>
@else
    <div class="flex items-center {{ $class }}" role="none">
        <div class="{{ $classes['border'] }} {{ $classes['margin'] }}"></div>
        <div class="px-6 text-sm md:text-sm text-slate-500">
            {{ $slot }}
        </div>
        <div class="{{ $classes['border'] }} {{ $classes['margin'] }}"></div>
    </div>
@endif
