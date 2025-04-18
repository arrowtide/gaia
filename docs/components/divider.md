# Divider
Renders a horizontal line with optional text.

[TOC]

## Usage

```html
<x-divider>
```

## Spacing

You can set the margin of the divider with the `space` attribute. There are 4 options: `sm`, `md`, `lg`, `xl`.

```html
<x-divider space="sm">
<x-divider space="md">
<x-divider space="lg">  
<x-divider space="xl">
```

## Seperator text

You can set the text (or any other content really, like an icon) of the divider by using the default slot.

```html
<x-divider>or</x-divider>
```

## Class

You can set the class of the divider with the `class` attribute. Useful if you wish to use your own margin that isn't already covered by the `space` attribute.

```html
<x-divider class="my-4">
```

## Reference 

### Properties

| Property | Description | Required |
| ----------- | ----------- | --------- |
| `space` | The top and bottom margin. `sm`, `md`, `lg`, `xl` |  |
| `class` | Any css class |  |

### Slots

| Slot | Description | Required |
| ----------- | ----------- | --------- |
| Default | The text or content of the divider. |  |
