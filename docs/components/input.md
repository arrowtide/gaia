# Input
Displays an input element.

[TOC]

## Usage

```html
<x-input name="email" label="Email" value="{{ old:email }}" />
```

## Label
If you want to display a label above the input, use the `label` attribute. 

```html
<x-input label="Email" />
```

## Reveal
Use the `reveal` property to add a show/hide trigger. Useful for passwords.

```html
<x-input reveal />
```

Behind the scenes, this turns the input type into `text` and back to the original type.


## Description
Use the `description` property to display a description above the input.

```html
<x-input description="Must be a valid email address" />
```

You can also use the `description_end` property to display a description below the input.

```html
<x-input description_end="Must be a valid email address" />
```

## Errors
This component will automatically display any errors that match the `name` attribute of the input that are found within the Laravel error bag. Statamic automatically does this for you within the built in form tags. 


## Reference 

### Properties

| Property | Description | Default | Required | 
| ----------- | ----------- | --------- | --------- |
| `reveal` | Displays a show/hide trigger | `false` |  |
| `description` | Displays a descripon above the input |  |  |
| `description_end` | Displays a descripon below the input |  |  |
| `*` | Any HTML attribute |  |  |
