# Modal 
A fullscreen modal popup.

[TOC]

## Usage

```html
<x-modal 
	name="basic" 
	heading="Delete Post" 
	subheading="Are you sure you want to delete that post? This action cannot be undone."
>
	<x-slot:buttons>
		<x-button variant="ghost" @click="$close()">Cancel</x-button>
		<x-button variant="danger" @click="$close()">Delete</x-button>
	</x-slot:buttons>
</x-modal>
```

## Name
The name is a required parameter and should be unique to the page and follow a slugified naming convention. The name you provide is used for opening and closing events.

```html
<x-modal name="delete-post">
	...
</x-modal>
```

## Heading and subheading
You may pass in a heading and a subheading to the modal.

```html
<x-modal heading="Delete Post" subheading="Are you sure you want to delete that post?">
```


## Opening 

**Livewire**
```php
$this->dispatch('modal:open:YOUR_NAME');
```

**Alpine JS**
```html
<button @click="$dispatch('modal:open:YOUR_NAME')">
	Open Modal
</button> 
```

**Vanilla JS**
```js
window.dispatchEvent(new CustomEvent('modal:open:YOUR_NAME'));
```

## Closing

**Livewire**
```php
$this->dispatch('modal:close:YOUR_NAME');
```

**Alpine JS**
```html
<button @click="$dispatch('modal:close:YOUR_NAME')">
	Open Modal
</button> 
```

**Vanilla JS**
```js
window.dispatchEvent(new CustomEvent('modal:close:YOUR_NAME'));
```

## Nesting
Although not recommended, you may nest modals within one another. It's important to pass in `overlay="false"` and `noscroll="false"` to the nested modal.

## Reference 

| Property | Description | Required |
| ----------- | ----------- | --------- |
| `name` | A slugified name of your component. Should be unique to the page. | Yes |
| `heading` | The heading of the modal. |  |
| `subheading` | The subheading / description of the modal. |  |
| `overlay` | Set to `false` to remove the overlay. |  |
| `noscroll` | Set to `false` to remove the AlpineJS scroll lock. |  |
| `duration` | The duration of the animation in milliseconds. |  |

## Slots

| Slot | Description |
| ----------- | ----------- |
| `x-slot:content` | Your body content. |
| `x-slot:buttons` | Quickly add buttons to the footer and have them automatically aligned. |
| `x-slot:footer` | Overrides the entire footer. |
