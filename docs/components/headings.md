# Headings

[TOC]

## Usage
```html
<x-heading level="2">Your heading</x-heading>
```

## Level

The heirarchical level (`h1`, `h2`, etc) of the heading can be set using the `level` property. Supports up to level `4`.

```html
<x-heading level="1">
<x-heading level="2">
<x-heading level="3">
<x-heading level="4">
```

## Size

The visual size of the heading can be set using the `size` property. Supports up to size `4`.

```html
<x-heading level="2" size="3">
```

## Reference 

### Properties

| Property | Description | Required |
| ----------- | ----------- | --------- |
| `level` | The heirarchical level of the heading | Yes |
| `size` | The visual size of the heading |  |
| `*` | Any HTML attribute |  |
