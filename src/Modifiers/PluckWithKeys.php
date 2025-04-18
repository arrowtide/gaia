<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Modifiers;

use ArrayAccess;
use Statamic\Entries\Collection;
use Statamic\Facades\Compare;
use Statamic\Modifiers\Modifier;
use Statamic\Support\Arr;

class PluckWithKeys extends Modifier
{
    /**
     * Plucks values from a collection of items.
     */
    public function index(array|Collection $value, array $params): Collection|array
    {

        if ($wasArray = is_array($value)) {
            $value = collect($value);
        }

        if (Compare::isQueryBuilder($value)) {
            $value = $value->get();
        }

        $items = $value->map(function ($item) use ($params) {
            $values = collect($params)->mapWithKeys(function ($param) use ($item) {
                if (is_array($item) || $item instanceof ArrayAccess) {
                    return [$param => Arr::get($item, $param)->raw()];
                } else {
                    $value = method_exists($item, 'value') ? $item->value($param) : $item->get($param);

                    return [$param => $value];
                }
            });

            return $values;
        });

        return $wasArray ? $items->all() : $items;
    }
}
