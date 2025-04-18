# Alert
Displays an inline alert box.

[TOC]

## Usage

```html
<x-alert>
    Bananas are berries, but strawberries aren't.
</x-alert>
```

## Variant

You can change the alert variant by setting the `variant` property to any of the following:
- `default`
- `success`
- `info`
- `warning`
- `danger`

## Title
Overwrite the title by using the `title` property.

```html
<x-alert title="Did you know?">
    Bananas are berries, but strawberries aren't.
</x-alert>
```

## Reference 

### Properties

| Property | Description | Default | Required | 
| ----------- | ----------- | --------- | --------- |
| `title` | Overwrite the title | Varied  |  |
| `variant` | The alert variant | `default` | |
