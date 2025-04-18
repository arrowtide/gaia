@props([
    'title' => false,
    'variant' => 'default',
])

@php 
    $titleText = [
        'default' => 'Note',
        'success' => 'Success',
        'error' => 'Error',
        'info' => 'Info',
        'warning' => 'Warning',
    ];

    $title = $title ? $title : $titleText[$variant];

    $icon = [
        'default' => 'info',
        'success' => 'check',
        'error' => 'error',
        'info' => 'info',
        'warning' => 'alert',
    ];

    $container = [
        'default' => 'text-slate-500 bg-slate-50 border-slate-500/50',
        'success' => 'text-emerald-500 bg-emerald-50 border-emerald-500/50',
        'error' => 'text-rose-500 bg-rose-50 border-rose-500/50',
        'info' => 'text-sky-500 bg-sky-50 border-sky-500/50',
        'warning' => 'text-yellow-500 bg-yellow-50 border-yellow-500/50',
    ];
@endphp

<div role="alert" class="relative w-full px-4 py-5 border rounded-lg {{ $attributes->get('class') }} {{ $container[$variant] }}">
    <s:svg src="{{ $icon[$variant] }}" class="absolute fill-current size-5 top-5.5 left-5" />
    <strong class="block pl-10 mb-1 font-semibold">{{  $title  }}</strong>
    <div class="pl-10 text-sm [&>a]:underline">
        {{  $slot  }}

        @if ($errors)
            <ol>
                @foreach($errors as $error)
                    <li>{{  $error  }}</li>
                @endforeach
            </ol>
        @endif
    </div>
</div>
