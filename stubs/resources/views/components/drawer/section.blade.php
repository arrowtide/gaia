<div class="px-5 sm:px-10  {{ $attributes->get('class') }}" {{ $attributes->except(['class']) }}>
    {{  $slot }}
</div>
