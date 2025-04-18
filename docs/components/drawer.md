# Drawer

A simple drawer that slides in from a side of the screen.

[TOC]

## Usage
```html
<x-drawer name="form" title="My Drawer">
    <x-drawer.section>
        <p>Drawer content</p>
    </x-drawer.section>
</x-drawer>
```

## Name
The name is a required parameter and should be unique to the page and follow a slugified naming convention. The name you provide is used for opening and closing events.

```html
<x-drawer name="delete-post">
	...
</x-drawer>
```

## Heading
The heading of the drawer.

```html
<x-drawer heading="My awesome form">
```

## Position
You can change which position the drawer slides in from using the `position` attribute. You can choose either `left` or `right`.

```html
<x-drawer position="left">
<x-drawer position="right">
```

## Opening 

**Livewire**
```php
$this->dispatch('drawer:open:YOUR_NAME');
```

**Alpine JS**
```html
<button @click="$dispatch('drawer:open:YOUR_NAME')">
	Open Modal
</button> 
```

**Vanilla JS**
```js
window.dispatchEvent(new CustomEvent('drawer:open:YOUR_NAME'));
```

## Closing

**Livewire**
```php
$this->dispatch('drawer:close:YOUR_NAME');
```

**Alpine JS**
```html
<button @click="$dispatch('drawer:close:YOUR_NAME')">
	Open Modal
</button> 
```

**Vanilla JS**
```js
window.dispatchEvent(new CustomEvent('drawer:close:YOUR_NAME'));
```

## Nesting
Although not recommended, you may nest modals within one another. It's important to pass in `overlay="false"` and `noscroll="false"` to the nested modal.

## Reference 

| Property | Description | Required |
| ----------- | ----------- | --------- |
| `name` | A slugified name of your component. Should be unique to the page. | Yes |
| `overlay` | Set to `false` to remove the overlay. |  |
| `noscroll` | Set to `false` to remove the AlpineJS scroll lock. |  |
| `duration` | The duration of the animation in milliseconds. |  |

## Slots

| Slot | Description |
| ----------- | ----------- |
| `x-slot:section` | Use this slot to add a padded section to the drawer. |

