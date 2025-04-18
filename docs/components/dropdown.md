# Dropdown

[TOC]

## Usage

```html
<x-dropdown>
	<x-slot:button>
		<x-button for="dropdown" icon="expand_more" variant="primary">Dropdown</x-button>
	</x-slot:button>

	<x-slot:content>
		<x-dropdown.menu>
			<x-dropdown.item>Profile</x-dropdown.item>
			<x-dropdown.item>Saved</x-dropdown.item>
			<x-dropdown.item>Orders</x-dropdown.item>
		</x-dropdown.menu>

		<x-dropdown.divider />

		<x-dropdown.menu>
			<x-dropdown.item>Sign Out</x-dropdown.item>
		</x-dropdown.menu>
	</x-slot:content>
</x-dropdown>
```

## Position / Anchoring
You can change the position of the dropdown with the `anchor` attribute. This uses the [AlpineJS anchor plugin](https://alpinejs.dev/plugins/anchor#positioning) so any position names that are supported there are supported here too.

```html
<x-dropdown anchor="right-end.offset.50">
```

## Button
Some extra steps are needed if you want to trigger the dropdown using a button. You need to add the `for="dropdown"` attribute if you are using the built in `<x-button>` component. If you are using a standard html trigger, you will need bind manually as seen below.

```html
<x-button for="dropdown">Dropdown</x-button>

<button type="button" x-ref="button" x-bind="button">Dropdown</button>
```

## Components

### `x-dropdown.menu`
The menu component gives you a simple container to wrap your dropdown items. 

```html
<x-dropdown>
	<x-dropdown.menu>
		<x-dropdown.item>Orders</x-dropdown.item>
		<x-dropdown.item>Saved</x-dropdown.item>
	</x-dropdown.menu>
</x-dropdown>
```

### `x-dropdown.item`
The item component is a simple button. Most of the properties are the same as the `<x-button>` component. 

```html
<x-dropdown>
	<x-dropdown.menu>
		<x-dropdown.item>Orders</x-dropdown.item>
		<x-dropdown.item>Saved</x-dropdown.item>
	</x-dropdown.menu>
</x-dropdown>
```

### `x-dropdown.divider`
The divider component is a simple divider that divides your content or menus.

```html
<x-dropdown>
	<x-dropdown.menu>
		<x-dropdown.item>Orders</x-dropdown.item>
		<x-dropdown.item>Saved</x-dropdown.item>
	</x-dropdown.menu>

	<x-dropdown.divider />

	<x-dropdown.menu>
		<x-dropdown.item>Sign Out</x-dropdown.item>
	</x-dropdown.menu>
</x-dropdown>
```

## Reference 

### Properties

| Property | Description | Required |
| ----------- | ----------- | --------- |
| `name` | A slugified name of your component. Should be unique to the page. |  |
| `anchor` | The position of the dropdown |  |

### Slots

| Slot | Description |
| ----------- | ----------- |
| `x-slot:button` | This slot should contain the button that triggers the dropdown. |
| `x-slot:content` | The main content of the dropdown. |

