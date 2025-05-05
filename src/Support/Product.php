<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Support;

use Statamic\Entries\Entry;
use Statamic\Facades\Site;

class Product
{
    /**
     * Returns the minimum price of the product variants.
     *
     * It will always return the lowest price, if there is a compare at price
     * it will use that, otherwise it will use the price set in the price field.
     *
     * @param Entry $product  The product entry.
     * @return float The minimum price of the product variants.
     */
    public static function minPrice(Entry $product): float
    {
        $variants = Entry::query()
            ->where('site', Site::current()->handle())
            ->where('collection', 'variants')
            ->where('product_slug', $product->slug())->get();

        $minPrice = $variants->min(function ($variant) {
            $price = $variant->price;
            $compareAtPrice = $variant->compare_at_price;

            if ($compareAtPrice && $compareAtPrice < $price) {
                return $compareAtPrice;
            }

            return $price;
        });

        return (float) ($minPrice ?? 0.0);
    }
}
