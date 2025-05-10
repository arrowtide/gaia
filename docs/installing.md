# Installing

[TOC]

Before installing, ensure that you are using a clean install of Statamic. You can learn [how to set this up locally](https://statamic.dev/installing/local). __Skip the step where Statamic asks if you want to install a starterkit.__ Gaia is packaged as an addon, so we'll install it later.

It's also __highly recommended that you use version control__ (git) before installing Gaia so you can track your changes and roll back if something goes wrong.

## 1. Installing Gaia
You can install Gaia with Composer:
```bash
composer require arrowtide/gaia
```

This will also install the following dependencies:
- [Livewire](https://statamic.com/addons/marcorieser/livewire)
- [Shopify](https://statamic.com/addons/rad-pack/shopify)
- [Payment Icons](https://statamic.com/addons/arrowtide/payment-icons)
- [Antlers Components](https://statamic.com/addons/stillat/antlers-components)



## 2. Running the Install Command
To streamline setup, use Gaia's built-in install command. This will automatically transfer all relevant template files to the correct directories:
```bash
php artisan gaia:install
```

## 3. Set up search
To get live search up and running we first need to head over to the `config/statamic/search.php` and add the following `indexes` configuration:
```php
    'indexes' => [
        'live-search' => [
            'driver' => 'local',
            'searchables' => ['collection:shop', 'collection:products'],
            'fields' => ['title', 'collections'],
        ],
    ],
```
You can read more about Statamic search indexes [here](https://statamic.dev/search#indexes).

## 4. Set up currency
Head over to `config/gaia.php` and set `default_currency` to your desired currency code. The code should be a valid [ISO 4217](https://www.iso.org/iso-4217-currency-codes.html) currency code.

If you're building a multisite store you can follow [these instructions](/multisite#currencies) instead.

## 5. Set up the Shopify addon
You're now ready to set up the Shopify addon. It is already installed, so you can skip the `composer require statamic-rad-pack/shopify` step.

[Please follow these steps](https://statamic-shopify-docs.vercel.app/setup).
