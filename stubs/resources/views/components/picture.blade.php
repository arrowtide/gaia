@props([
    'src' => null,
    'lazy' => true,
    'fit' => 'crop_focal',
    'alt' =>  null,
])

@php
    if ($src instanceof Statamic\Assets\Asset) {
        $url = $src->url();
        $alt = $alt ?? $src->data()['alt'] ?? '';
    } else {
        $url = $src;
    }

    $sizes = [];

    $screens = [
        'default' => 0,
        'xs' => 420,
        'sm' => 600,
        'md' => 800,
        'lg' => 1000,
        'xl' => 1200,
        '2xl' => 1500,
        '3xl' => 1900,
    ];

    collect($attributes->all())->each(function ($value, $key) use (&$sizes, $screens) {
        if (\Illuminate\Support\Str::startsWith($key, 'size.')) {
            
            [$indicator, $query, $dimension] = explode('.', $key);

            // Check if the query exists in the screens array
            if (\Illuminate\Support\Arr::has($screens, $query)) {
                $query = $screens[$query];
            }

            // Initialize the array if it doesn't exist
            if (!isset($sizes[$query])) {
                $sizes[$query] = [];
            }

            $sizes[$query][$dimension] = $value;
        }
    });

    $sizes = collect($sizes)->sortKeysDesc()->toArray();
@endphp

<picture @if (isset($picture)) {{ $picture->attributes ?? '' }} @endif>
    @foreach($sizes as $query => $dimensions)
        <source 
            srcset="{{ Statamic::tag('glide')->src($url)->width($dimensions['width'])->height($dimensions['height'])->fit($fit)->format('webp')->fetch() }},
            {{ Statamic::tag('glide')->src($url)->width($dimensions['width'])->height($dimensions['height'])->fit($fit)->dpr(2)->format('webp')->fetch() }} 2x"
            media="(min-width: {{ $query }}px)" 
            type="image/webp"
        >
    @endforeach
    <img 
        src="{{ Statamic::tag('glide')->src($url)->width($sizes[0]['width'])->height($sizes[0]['height'])->format('webp')->fetch() }}"
        alt="{{  $alt }}" 
        width="{{ $sizes[0]['width'] }}"
        height="{{ $sizes[0]['height'] }}" 
        loading="{{ $lazy ? 'lazy' : 'eager' }}"
        @if (isset($img)){{ $img->attributes->except(['src', 'alt', 'width', 'height', 'loading']) ?? '' }}@endif
    >
</picture>
