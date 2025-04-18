@props([
    'as' => 'button',
    'type' => 'button',
    'class' => '',
    'title' => '',
    'icon' => '',
])

<{{ $as }} 
    type="button" 
    class="flex-col h-full px-3 sm:px-4 py-2 btn btn-ghost {{  $class  }}"
    {{ $attributes }}
>
    <s:svg src="{{  $icon }}" class="fill-current !size-6" />
    <span class="text-xs sr-only sm:not-sr-only">
        {{  $title  }}

        @if (isset($aftertitle))
            {{ $aftertitle }}
        @endif
    </span>
    {{ $slot }}
</{{ $as }}>
