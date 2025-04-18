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

    public function livewireStart(): string
    {
        return <<<'JS'
            <script type="module">
                setTimeout(() => {
                    Livewire.start();
                }, 0);
            </script>
        JS;
    }

    /**
     * Gets the parent product from a variant slug
     */
    // public function parentProductFromVariant()
    // {
    //     $slug = $this->params->get('slug');

    //     $entries = Entry::query()
    //         ->where('collection', 'variants')
    //         ->where('slug', $slug)
    //         ->get();

    //     $products = [];

    //     foreach ($entries as $variant) {
    //         $product = Entry::query()
    //             ->where('collection', 'products')
    //             ->where('slug', $variant->get('product_slug'))
    //             ->first();

    //         if ($product) {
    //             $products[] = $product->toArray();
    //         }

    //         return $products;
    //     }
    // }

    public function head()
    {
        return <<<'SCRIPT'
        <script>
        //     (async () => {
        //         try {
        //             await fetch('/test-cart', {
        //                 method: "GET",
        //                 headers: {
        //                     "Content-Type": "application/json",
        //                     'X-Requested-With': 'XMLHttpRequest',
        //                 }
        //             });
        //         } catch (error) {
        //             console.error(error);
        //         }
        //     })();
        </script>
        SCRIPT;
    }

    // public function wishlistSingleGetProducts()
    // {
    //     $user = User::current();

    //     if (! $user) {
    //         return;
    //     }

    //     if (! $user->data()->get('wishlist')) {
    //         return false;
    //     }

    //     $wishlistedProducts = $user->data()->get('wishlist')['collections'][0]['items'];

    //     $products = Entry::query()
    //         ->where('site', Site::current()->handle())
    //         ->where('collection', 'products')
    //         ->whereIn('product_id', $wishlistedProducts);

    //     return $products;
    // }

    public function currency(): string
    {
        return Number::currency(
            (float) $this->params->get('price', 0.0),
            Site::current()->attribute('currency') ?? config('gaia.default_currency'),
            Site::current()->locale()
        );
    }
}
