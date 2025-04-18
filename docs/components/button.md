# Button

A button tag is used to render a button.

[TOC]

## Usage

```html
<x-button>My Button!</x-button>
```

## Variants
You may change the variant of the button with the `variant` attribute.

```html
<x-button variant="primary">
<x-button variant="secondary">
<x-button variant="ghost">
<x-button variant="danger">
<x-button variant="link">
<x-button variant="blank">
```

## Square and round
To ensure a button is square or round, you can use the `class` property.

```html
<x-button label="Close" icon="close" class="btn-square">
<x-button label="Close" icon="close" class="btn-round">
```

Since you can't fit text inside of a square or round button, you'll need to use the `label` property to add accessible text.

## Sizes
You can change the size of the button with the `class` property.

```html
<x-button class="btn-xs">
<x-button class="btn-sm">
<x-button class="btn-base">
<x-button class="btn-lg">
```

Because the button sizes are Tailwind utilities, you can use them to create a responsive button:

```html
<x-button class="btn-sm sm:btn-base lg:btn-lg">
```

## Icons
Add an icon with the `icon` property. The icon uses the [svg tag](https://statamic.dev/tags/svg) under the hood, so the name should be in one of the paths.

```html
<x-button icon="arrow_left_alt">
```

You can change the position of an icon by using the `icon_position` attribute, and passing either `start` or `end`. The default is `start`. 

```html
<x-button icon="arrow_left_alt" icon_position="end">
```

## As
By default, the element will be a `<button>`. You can change this with the `as` attribute.

```html
<x-button as="a" href="/">
```

## Loading indictator
You may display a loading indicator with the `loading` property. The value should corrospond to an AlpineJS variable. 

```html
<div x-data="{isLoading: false}"> 
    <x-button loading="isLoading" @click="isLoading = true">Click me</x-button>
</div>
```

## Attributes
The button also supports any HTML attribute. Although if you're using AlpineJS you will need to use the longhand versions of directives like `x-on`, `x-bind` etc. 

```html
<x-button type="submit" data-button="awesome" x-on:click="alert('Hello!')">
```

## Reference 

### Properties

| Property | Description | Required |
| ----------- | ----------- | --------- |
| `variant` |  |  |
| `heading` | The heading of the modal. |  |
| `subheading` | The subheading / description of the modal. |  |
| `overlay` | Set to `false` to remove the overlay. |  |
| `noscroll` | Set to `false` to remove the AlpineJS scroll lock. |  |
| `*` | Any HTML attribute |  |
