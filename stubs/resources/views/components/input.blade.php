@props([
    'name' => null,
    'type' => 'text',
    'label' => null,
    'description' => null,
    'description_end' => null,
    'reveal' => false,
    'required' => null,
])

@php
    $identifier = 'form-field-'.Str::random(10);

    /** @var \Illuminate\Support\ViewErrorBag */
    $errorMessageBags = view()->shared('errors');

    $inputErrorMessages = collect($errorMessageBags->getBags())
        ->flatMap(fn ($bag) => $bag->messages())
        ->filter(fn ($_, $key) => Str::is($name, $key))
        ->flatten()
        ->toArray();
@endphp

@if ($label || $description || $description_end)<div class="grid gap-2 {{ $attributes->get('field:class') }}">@endif
    @if ($label)
    <x-label class="form-label" for="{{ $identifier }}">
        {{ $label }} @if ($required && $required  !== 'input') <span class="text-sm text-rose-500 align-super">*</span> @endif
    </x-label>@endif
    @if ($description)<p class="pr-4 mb-1 text-sm text-slate-500">{{ $description }}</p>@endif

    @if ($reveal)
    <div 
        class="relative"
        x-data="{
            show: false,
            inputType: '{{ $type }}',
            buttonText: '{{ __('Reveal input') }}',
            statusText: '',
            togglePassVisibility() {
                this.show = !this.show;
                this.inputType = this.show ? 'text' : '{{  $type  }}';
                this.buttonText = this.show ? '{{ __('Conceal input content') }}' : '{{ __('Reveal input content') }}';
                this.statusText = this.show ? '{{ __('Input content is visible') }}' : '{{ __('Input content is concealed') }}';
            }
        }"
    >
    @endif
        <input 
            {{  $attributes->except(['type', 'class']) }}
            type="{{  $type  }}"
            class="form-input w-full @if($reveal) pr-12 @endif {{ $attributes->get('class') }}"
            id="{{ $identifier }}"
            @if ($name) name="{{ $name }}" @endif
            @if ($reveal) :type="inputType" @endif
            @if ($required) required @endif
        />
    
    @if ($reveal)
        <p class="sr-only" aria-live="polite" x-text="statusText"></p>
        <button
            type="button"
            @click="togglePassVisibility()"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm leading-5 transition-opacity duration-500 ease-linear cursor-pointer group/reveal starting:opacity-0"
            aria-controls="{{ $identifier }}"
            :data-visible="show"
            x-cloak
        >
            <s:svg src="visibility" class="block w-6 group-data-visible/reveal:hidden" aria-hidden="true" />
            <s:svg src="visibility_off" class="hidden w-6 group-data-visible/reveal:block" aria-hidden="true" />
            <span class="sr-only" x-text="buttonText"></span>
        </button>
    </div>
    @endif
    
    @if ($description_end)<p class="pr-4 mb-1 text-sm text-slate-500">{{ $description_end }}</p>@endif

    @if (! empty($inputErrorMessages))
        <div class="grid gap-2 @if (! ($label || $description || $description_end)) mt-2 @endif">
            @foreach($inputErrorMessages as $message)
                <p class="text-sm text-rose-500" id="input-error-{{ $identifier }}">
                    <s:svg src="error" class="inline w-4 mr-1 fill-current" aria-hidden="true" />
                    {{ $message }}
                </p>
            @endforeach
        </div>
    @endif
@if ($label || $description || $description_end)</div>@endif
