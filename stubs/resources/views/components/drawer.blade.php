@props([
    'name' => null,
    'heading' => '',
    'position' => 'right',
    'overlay' => true,
    'parents' => '',
    'noscroll' => true,
])

@php 
    $duration = 300;
    
    if (! $name) {
        throw new Exception('You must provide a name for the drawer component.');
    }

    if ($position !== 'left' && $position !== 'right') {
        throw new Exception('Drawer position must be "left" or "right"');
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
    x-data="drawerComponent('{{  $name }}', $el, {{  $duration }}, {{  json_encode(array_filter(explode('|', $parents))) }})"
    data-drawer-component="{{  $name  }}"
    class="fixed inset-0 overflow-hidden bg-transparent select-none w-dvw h-dvh min-w-dvw min-h-dvh backdrop:hidden"
    @drawer:open:{{ $name }}.window="openDrawer()"
    @drawer:close:{{ $name }}.window="closeDrawer()"
    @keydown.escape.prevent="closeDrawer()"
    {{  $attributes->except(['class'])  }}
    x-cloak
>
    <div
        class="absolute w-full sm:max-w-[500px] focus-visible:outline-none h-dvh bg-white z-50 transform-gpu transition-discrete translate-x-0 transition-all ease-in-out {{ $classes['radius'][$position] }} {{ $classes['position'][$position] }}"
        x-show="open"
        data-drawer-inner
        x-trap.inert{{ $noscroll ? '.noscroll' : '' }}="open"
        x-transition:enter-start="{{ $classes['animation'][$position]['enter-start'] }}"
        x-transition:enter-end="{{ $classes['animation'][$position]['enter-end'] }}"
        x-transition:leave-start="{{ $classes['animation'][$position]['leave-start'] }}"
        x-transition:leave-end="{{ $classes['animation'][$position]['leave-end'] }}"
        @keydown.escape="close()"
        x-cloak
        x-ref="drawer"
        tabindex="-1"
        style="transition-duration: {{  $duration }}ms;"
    >
        <div class="relative flex flex-col h-full overflow-auto">
            <header class="py-4 border-b border-b-slate-200">
                <x-drawer.section class="flex items-center justify-between">
                    @if (isset($back))
                        {{ $back }}
                    @endif  
                    <x-heading level="2" size="4" class="w-full text-center">{{ $heading }}</x-heading>
                    <x-button label="Close" variant="ghost" icon="close" class="btn-round" @click="closeAllDrawers()" />
                </x-drawer.section>
            </header>
            
            <div class="overflow-auto grow">
                {{  $slot  }}
            </div>

            @if (isset($footer))
                <footer>
                    {{  $footer  }}
                </footer>
            @endif
        </div>
    </div>

        <div
            class="duration-300 overlay ease"
            x-show="open"
            @click="closeAllDrawers()"
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

