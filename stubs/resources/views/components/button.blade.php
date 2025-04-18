@props([
    'variant' => 'primary',
    'icon' => null,
    'label' => null,
    'icon_class' => 'w-auto h-3/6 fill-current',
    'icon_position' => 'start',
    'as' => 'button',
    'type' => 'button',
    'class' => '',
    'loading' => false,
    'text' => '',
    'for' => '',
])

@php
    $variantClass = [
        'primary' => 'btn btn-primary',
        'secondary' => 'btn btn-secondary',
        'ghost' => 'btn btn-ghost',
        'danger' => 'btn btn-danger',
        'link' => 'btn-link',
        'blank' => 'btn',
    ][$variant] ?? 'btn btn-primary';

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
    class="group/button {{ $loading ? 'relative' : '' }} {{ $variantClass }} {{ $class }}"
    {{ $processedAttributes->except(['class', 'type']) }}
    @if ($as === 'button') type="{{ $type }}" @endif
    @if ($loading) :data-loading="{{ $loading }}" @endif

    {{-- TODO: See if we can make this work by using parent data --}}
    @if ($for == 'dropdown')
        x-ref="button"
        x-bind="button"
    @endif
>
    @if ($loading)
        <span class="absolute inset-0 flex items-center justify-center opacity-0 pointer-events-none group-data-loading/button:opacity-100" x-ignore>
            <s:partial:components/loader />
        </span>
    @endif

    @if ($loading)
        <span class="inline-flex gap-2 group-data-loading/button:opacity-0" x-ignore>
    @endif

    @if ($icon)
        <s:svg :src="$icon" class="{{ $icon_class }} {{ $icon_position_class }}" aria-hidden="true" />
    @endif

    @if (! $slot->isEmpty())
        <span @if ($label) aria-hidden="true" @endif>
            {{ $slot }}
        </span>
    @endif

    @if ($label)
        <span class="sr-only">{{ $label }}</span>
    @endif

    @if ($loading)
        </span>
    @endif
</{{ $as }}>
