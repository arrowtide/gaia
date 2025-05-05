# Tags

[TOC]


## Introduction
All tags are prefixed with `gaia`.


## currency
Formats a number to a currency that is defined in your `sites`. If you don't have the currency attribute set, it will use `default_currency` defined inside of `gaia.php` config. 

[Learn more about defining currencies per-site.](#)

```twig
{{ gaia:currency price="12.00" }}

$12.00
```


## id
A nifty little utility to generate a unique ID for anything. Useful for when you use the same component multiple times on the same page but need unique names for them. 

```twig
{{ gaia:id }} //1h12931y2h1
```

Attach it to a variable to make it reusable:

```html
{{ $sliderId = {gaia:id} }}

<div data-swiper-slider="{{ $sliderId }}">
	...
</div>

<script>
	const swiper = new Swiper('[data-swiper-slider="{{ $sliderId }}"]', {
		...
	});
</script>
```

## siteCurrency
Returns the current currency that is being used by the site. This will either be the `default_currency` defined in `gaia.php` or the `currency` attribute set on the site if you're on a multisite setup.

```twig
{{ gaia:site_currency }}
```


## product:single
Returns `true` if the current product is a single product that has no variants, or `false` if it does.

```twig
{{ gaia:product:single }}


{{ if {gaia:product:single} }}
    Do something if it's a single product
{{ /if }}
```

## product:configurable
Returns `true` if the product is configurable and has variants, or `false` if it's not.

```twig
{{ gaia:product:configurable }}


{{ if {gaia:product:configurable} }}
    Do something if it's a configurable product
{{ /if }}
```


## product:price
Returns an array of prices for the current product. 

```twig
{{ gaia:product:price }}
    ...
{{ /gaia:product:price }}
```

You have all of these variables available to you inside of the `gaia:product:price` tag. 


```twig
<!-- Minimum price, before discounts -->
{{ min_display_price }}

<!-- Maximum price, before discounts -->
{{ max_display_price }}

<!-- Minimum price, after discounts. null if there are no discounts -->
{{ min_compare_at_price }}

<!-- Maximum price, after discounts. null if there are no discounts  -->
{{ max_compare_at_price }}

<!-- Returns true if it's discounted, false if it's not  -->
{{ is_discounted }}

<!-- The amount it's been discounted by -->
{{ discount_amount }}

<!-- The amount it's been discounted by as a percentage -->
{{ discount_percentage }}

<!-- If the price is uniform across all variants, this will be true -->
{{ is_uniform_price }}
```

## product:options
Returns a list of options for the current product.  

```twig
{{ gaia:product:options }}
```

```php
[
    [
        'name' => 'Plant Size',
        'type' => 'button',
        'values' => [
            ['value' => 'Large (30-60cm)'],
            ['value' => 'Small (10-30cm)']
        ],
        'option' => 1
    ],
    [
        // Option 2...
    ],
    [
        // Option 3...
    ]
]
```

## customer:order
Gets a customer order by order ID.

```twig
{{ gaia:customer:order id="88937123" }}
    Do stuff with the order...
{{ /gaia:customer:order }}
```

To see all of the data available to you:
```twig
{{ {gaia:customer:order id="98231923    } | dump }}
```

If no order is found, `no_results` is available.
```twig
{{ gaia:customer:order id="88937123 }}
    {{ if no_results }}
        Do something if there are no results
    {{ /if }}
{{ /gaia:customer:order }}
```


## customer:orders
Gets a list of customer orders.

Gets the orders for the current customer.

```twig
{{ gaia:customer:orders }}
    ...
{{ /gaia:customer:orders }}
```

To see all of the data available to you:
```twig
{{ {gaia:customer:orders} | dump }}
```

If no results are found, `no_results` is available.
```twig
{{ gaia:customer:orders }}
    {{ if no_results }}
        Do something if there are no results
    {{ /if }}
{{ /gaia:customer:orders }}
```

## wishlist:contains
Returns `true` if the product is inside of a wishlist. If you have wishlist collections turned on (multiple wishlists), this will return `true` if the product is inside _any_ of the wishlist collections. 

It's required you pass in the `product_id` key that is on your parent product.

```twig
{{ gaia:wishlist:contains :product_id="9123513532718" }}


<!-- If you need it as a string for JS, use the bool_string modifier  -->
{{ { gaia:wishlist:contains :product_id="product_id"} | bool_string }}
```
