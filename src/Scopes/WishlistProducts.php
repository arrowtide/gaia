<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Scopes;

use Statamic\Facades\User;
use Statamic\Query\Builder;
use Statamic\Query\Scopes\Scope;

class WishlistProducts extends Scope
{
    /**
     * Apply the scope.
     *
     * @param  Builder  $query
     * @param  array  $values
     */
    public function apply($query, $values): void
    {
        $wishlistCollection = 'default';

        $userData = User::current()->get('wishlist')['collections'] ?? [];

        $products = collect($userData)->firstWhere('id', $wishlistCollection)['items'] ?? [];

        $query->whereIn('product_id', $products);
    }
}
