<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Modifiers;

use Statamic\Modifiers\Modifier;

class TrimShopifyId extends Modifier
{
    public function index(string $value): string
    {
        $shopifyGidPrefixes = [
            'gid://shopify/Customer/',
            'gid://shopify/Product/',
            'gid://shopify/Order/',
            'gid://shopify/Collection/',
            'gid://shopify/Variant/',
            'gid://shopify/LineItem/',
        ];

        foreach ($shopifyGidPrefixes as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return str_replace($prefix, '', $value);
            }
        }

        return $value;
    }
}
