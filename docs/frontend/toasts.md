# Toasts
Toasts are small notifications that appear in the corner of the screen and are used to alert the user or provide feedback of an action without disrupting their experience.

[TOC]

## Triggering
You can trigger a toast with Javascript:

```js
gaia.toast({
    text: `This is an success status!`,
    type: 'success'
});
```

## Variants
There are four variants of toasts which you can use by changing the `type` key:

```js
gaia.toast({
    text: `This is an success status!`,
    type: 'success'
});

gaia.toast({
    text: `This is a warning status!`,
    type: 'success'
});

gaia.toast({
    text: `This is an error status!`,
    type: 'error'
});

gaia.toast({
    text: `This is an info status!`,
    type: 'info'
});
```

## Styling
You can style the toasts with Tailwind CSS from the `resources/views/layout/toasts/toasts.antlers.html` file.

## Accessibility
Toast notifications have a role of `status`. This is a [live region](https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Guides/Live_regions) that is meant to provide advisory information. 

Due to being a live region, it is not recommended to use a status right before navigating away from the page, as this could lead to a confusing experience. 



