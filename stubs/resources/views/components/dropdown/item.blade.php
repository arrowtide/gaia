@props([
    'icon' => null,
    'icon_class' => 'w-auto h-3/6 fill-current',
    'icon_position' => 'start',
    'as' => 'button',
    'type' => 'button',
    'class' => '',
])

@php
    $icon_position_class = $icon_position === 'end' ? 'order-2' : '';

    // We need to map attributes that are boolean true or false
    // and convert them to strings instead. 
    $processedAttributes = new \Illuminate\View\ComponentAttributeBag(
        collect($attributes->getAttributes())->map(function ($value) {
            if ($value === true) {
                return 'true';
            }
            if ($value === false) {
                return null;
            }
            return $value;
        })->all()
    );
@endphp

<{{ $as }}
    class="group/button btn btn-ghost {{ $class }}"
    {{ $processedAttributes->except(['class']) }}
    @if ($as === 'button') type="{{ $type }}" @endif
>
    @if ($icon)
        <s:svg :src="$icon" class="size-4.5 {{ $icon_position_class }}" aria-hidden="true" />
    @endif

    <span>
        {{ $slot  }}
    </span>
</{{ $as }}>
