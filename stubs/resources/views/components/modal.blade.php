@props([
    'name' => null,
    'heading' => '',
    'subheading' => null,
    'overlay' => true,
    'noscroll' => true,
    'duration' => 300,
])


@php 

    $duration = 300;
    
    // Ensure the name is set, or set an exception
    if (! $name) {
        throw new Exception('You must provide a unique name for the drawer component.');
    }

    $classes = [
        'radius' => [
            'left' => 'sm:rounded-r-xl',
            'right' => 'sm:rounded-l-xl'
        ],
        'position' => [
            'left' => 'left-0',
            'right' => 'right-0'
        ],
        'animation' => [
            'left' => [
                'enter-start' => 'opacity-0 !-translate-x-10',
                'enter-end' => 'opacity-100 !translate-x-0',
                'leave-start' => 'opacity-100 !translate-x-0',
                'leave-end' => 'opacity-0 !-translate-x-10',
            ],
            'right' => [
                'enter-start' => 'opacity-0 !translate-x-10',
                'enter-end' => 'opacity-100 !translate-x-0',
                'leave-start' => 'opacity-100 !translate-x-0',
                'leave-end' => 'opacity-0 !translate-x-10',
            ],
        ]
    ];
@endphp

<dialog 
    x-data="modalComponent('{{  $name }}', $el, {{ $duration }})"
    data-modal-component="{{  $name  }}"
    class="fixed inset-0 overflow-hidden bg-transparent select-none w-dvw h-dvh min-w-dvw min-h-dvh backdrop:hidden"
    @modal:open:{{ $name }}.window="$open()"
    @modal:close:{{ $name }}.window="$close()"
    @keydown.escape.prevent="$closeAll()"
    {{  $attributes->except(['class'])  }}
    x-cloak
>
    <div class="flex items-center justify-center w-full h-full">
        <div
            class="absolute overflow-hidden w-full sm:max-w-[500px] max-h-[90vh] bg-white z-50 rounded-xl transform-gpu transition-discrete translate-y-0 transition-all ease-in-out"
            x-show="open"
            data-modal-inner
            x-trap.inert{{ $noscroll ? '.noscroll' : '' }}="open"
            x-transition:enter-start="opacity-0 !translate-y-5"
            x-transition:enter-end="opacity-100 !-translate-y-0"
            x-transition:leave-start="opacity-100 !-translate-y-0"
            x-transition:leave-end="opacity-0 !translate-y-5"
            @keydown.escape="close()"
            x-cloak
            x-ref="modal"
            style="transition-duration: {{  $duration }}ms;"
        >
        <div class="max-h-[90vh] overflow-auto p-6">
                <x-button label="Close" variant="ghost" icon="close" class="absolute z-40 btn-round btn-small top-3 right-3" @click="$close()" />

                <div class="grid overflow-auto gap-y-5">
                    @if ($heading || $subheading)
                        <header class="grid gap-y-2">
                            @if ($heading)
                                <x-heading level="2" size="4" class="pr-6 text-left" id="modal-title-{{ $name }}">{{ $heading }}</x-heading>
                            @endif
                            @if ($subheading)
                                <div class="text-sm leading-normal text-slate-600">
                                    {{ $subheading }}
                                </div>
                            @endif
                        </header>
                    @endif

                    {{ $content ?? '' }}

                    @if ($footer ?? null)
                        {{ $footer }}
                    @elseif ($buttons ?? null)
                        <div class="flex justify-end gap-2 mt-4">
                            {{ $buttons }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div
        class="duration-300 select-none overlay ease"
        x-show="open"
        @click="$closeAll()"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak

        @if ($overlay == false)
            style="opacity: 0 !important;"
        @endif
    ></div>
</dialog>
