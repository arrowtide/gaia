<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Tags;

use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Ramsey\Uuid\UuidInterface;
use Statamic\Facades\Site;
use Statamic\Tags\TagNotFoundException;
use Statamic\Tags\Tags;

class Gaia extends Tags
{
    private array $classes = [
        'customer' => CustomerTags::class,
        'product' => ProductTags::class,
        'wishlist' => WishlistTags::class,
        'store' => StoreTags::class,
        'util' => UtilTags::class,
    ];

    public function wildcard(string $tag)
    {
        $tag = explode(':', $tag);
        $category = $tag[0];
        $method = Str::camel($tag[1]);

        if (isset($tag[2])) {
            $wildcard = $tag[2];
        }

        $class = $this->classes[$category] ?? null;

        if (! $class) {
            throw new TagNotFoundException("Tag {{ {$category}:{$method} }} not found class");
        }

        if (method_exists($class, $method)) {
            if (isset($wildcard)) {
                return (new $class($this))->{$method}($wildcard);
            } else {
                return (new $class($this))->{$method}();
            }
        }

        throw new TagNotFoundException("Tag {{ {$category}:{$method} }} not found");
    }

    public function uuid(): UuidInterface
    {
        return Str::uuid();
    }

    public function id()
    {
        return Str::lower(Str::random(10));
    }

    /**
     * Retrieves the current site's currency.
     *
     * @return string The currency code for the current site.
     *                If not set, returns the default currency from configuration.
     */
    public function siteCurrency(): string
    {
        return Site::current()->attribute('currency') ?? config('gaia.default_currency');
    }

    public function currency(): string
    {
        return Number::currency(
            (float) $this->params->get('price', 0.0),
            Site::current()->attribute('currency') ?? config('gaia.default_currency'),
            Site::current()->locale()
        );
    }
}
