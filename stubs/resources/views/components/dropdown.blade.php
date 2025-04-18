@props([
    'name' => null,
    'anchor' => 'bottom-start',
])

@php
    $dropdownName = $name ?? Str::random(8); // Fallback for unique ID if name is not provided
@endphp

<div 
    x-data="componentDropdown('{{ $dropdownName }}', '{{ $anchor }}')" 
    class="inline-block"
    x-bind="container"
>
    {{ $button }}

    <div
        class="fixed z-40 py-1.5 mt-2 overflow-auto bg-white border rounded-lg shadow-lg min-w-52 border-slate-300 content-visibility-auto w-fit"
        x-cloak
        x-bind="dropdown"
        x-ref="dropdown"
    >
        {{ $content  }}
    </div>
</div>
