# Picture 

The picture component lets you display a responsive image. 

[TOC]

## Usage
```html
<x-picture
    :src="featured_image"
    size.default.width="62"
    size.default.height="62"
    lazy="false"
/>
```

## Source
Use the `src` attribute to set the source url and alternative text of the image. This should either be an instance of `Statamic\Assets\Asset` or a string for your url.

In other words, if you have a field called `image` which allows you to select a single image you can use:

```html
<x-picture 
    src="{{ $image }}" 
    size.default.width="150"
    size.default.height="150"
/>
```

If you pass in an instance of `Statamic\Assets\Asset`, the alt text will automatically be set. 

## Sizes

To display different size images for different screen sizes, you can use the `size` attribute.

The property name should be:
- size - `query` - `dimension`

`query` - The screen size you want to display the image for. You may use shorthands defined inside of the `screens` array in`picture.blade.php`, or any pixel value.

`dimension` - Either `width` or `height`

**The picture component is mobile-first. For example, `default` is `0px` and above, `md` is `800px` and above, etc.**


### Examples

An avatar that displays `50x50` on all screen sizes.

```html
<x-picture
    size.default.width="50"
    size.default.height="50"
/>
```

A full screen banner that displays as `400x200` on mobile, `800x400` on tablet, and `1200x600` on desktop.

```html
<x-picture
    size.default.width="400"
    size.default.height="200"
    size.sm.width="800"
    size.sm.height="400"
    size.md.width="1200"
    size.md.height="600"
/>
```

## Lazy Loading
Lazy loading is enabled by default. You can disable it by setting the `lazy` attribute to `false`.

(This will change the attribute to `eager` instead.)

## Pixel Density
The picture component automatically generates images for `1x` and `2x` pixel density.

## Glide
To keep things simple, we only use the `fit` attribute currently.

## HTML Attributes
You can add any HTML attributes to either the `img` or the `picture` tag by using slots.

```html
<x-picture>
    <x-slot:picture class="overflow-hidden aspect-square" />
</x-picture>
```

```html
<x-picture>
    <x-slot:img fetchpriority="high" />
</x-picture>
```

## Reference 

### Properties

| Property | Description | Default |Required | 
| ----------- | ----------- | --------- | --------- |
| `src` | The source url of the image |  | Yes |
| `alt` | Alternative text of the image |  | Yes |
| `fit` | [The fit of the image](https://statamic.dev/tags/glide#-fit) | `crop_focal`  |
| `lazy` | The top and bottom margin | `true`  |

### Slots

| Slot | Description |
| ----------- | ----------- |
| `x-slot:picture` | Use to add html attributes to the `<picture>` element | 
| `x-slot:img` | Use to add html attributes to the `<img>` element |
