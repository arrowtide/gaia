<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Tags;

use Statamic\Facades\User;

class WishlistTags extends SubTag
{
    public function contains(): bool
    {
        if (! User::current()) {
            return false;
        }

        $collections = User::current()->data()['wishlist']['collections'] ?? [];
        $productId = $this->params->get('product_id');

        if (empty($collections)) {
            return false;
        }

        foreach ($collections as $collection) {
            if (isset($collection['items']) && in_array($productId, $collection['items'])) {
                return true;
            }
        }

        return false;
    }
}
