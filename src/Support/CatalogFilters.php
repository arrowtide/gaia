<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

class CatalogFilters
{
    protected array $filterMap;

    /**
     * Initializes a new instance of CatalogFilters with an empty filter map.
     */
    public static function initMap(): self
    {
        $instance = new self;
        $instance->filterMap = [];

        return $instance;
    }

    /**
     * Adds a new map to the existing filter map.
     *
     * This method merges the provided map into the current filter map,
     * allowing for additional filters to be included.
     *
     * @param  array  $map  The map of filters to be added.
     * @return self Returns the instance of the class for method chaining.
     */
    public function add(array $map): self
    {
        $this->filterMap = array_merge(
            $this->filterMap,
            $map
        );

        return $this;
    }

    /**
     * Generates and returns the filter map for a given URL.
     *
     * This function attempts to retrieve the filter map from the cache if caching is enabled.
     * If it's not found in the cache, the filter map is stored in the cache and then returned.
     *
     * @param  string  $url  The URL used to generate the cache key for storing or retrieving the filter map.
     * @return array The filter map associated with the provided URL.
     */
    public function generate(string $url): array
    {
        $key = "gaia/filters/{$url}";

        if (config('gaia.filtering.cache_enabled')) {
            if (Cache::has($key)) {
                return json_decode(Cache::get($key), true);
            }

            Cache::put($key, json_encode($this->filterMap), config('gaia.filtering.cache_duration'));
        }

        return $this->filterMap;
    }

    /**
     * Checks if a cached filter map exists for the provided URL.
     *
     * @param  string  $url  The URL associated with the filter map to check.
     * @return bool Returns true if a cached filter map exists, false if not.
     */
    public static function hasCachedMap(string $url): bool
    {
        $key = "gaia/filters/{$url}";

        return config('gaia.filtering.cache_enabled') && Cache::has($key);
    }

    /**
     * Retrieves the cached filter map for the provided URL.
     *
     * This function requires that the cache key exists and that the cache key is valid JSON.
     *
     * @param  string  $url  The URL associated with the filter map to retrieve.
     * @return array The cached filter map associated with the provided URL.
     */
    public static function getCachedMap(string $url): array
    {
        $key = "gaia/filters/{$url}";

        return json_decode(Cache::get($key), true);
    }

    /**
     * Sorts the collection of products by price.
     *
     * @param  string  $order  The order of the sort. Accepted values are 'asc' and 'desc'.
     *                         Defaults to 'asc'.
     */
    public static function sortByPrice(EntryCollection $products, string $order = 'asc'): EntryCollection
    {

        if ($order !== 'asc' && $order !== 'desc') {
            throw new \InvalidArgumentException('Invalid order value. Accepted values are "asc" and "desc".');
        }

        return $products
            ->map(function ($product) {
                $minPrice = Product::minPrice($product);
                $product->min_price = $minPrice;

                return $product;
            })
            ->sortBy('min_price', SORT_REGULAR, $order === 'desc')
            ->values();
    }

    /**
     * Generate a map of product metadata filters and their corresponding products.
     *
     * @param  EntryCollection  $products  The collection of products to generate the map for.
     * @return array An associative array where the keys are the filter names
     *               and the values are arrays of filter options, each containing
     *               the filter name, value, model, product count, and an array of
     *               product slugs.
     */
    public static function generateProductMetadataMap(EntryCollection $products): array
    {
        $attributes = collect(config('gaia.filtering.filters'))->where('type', 'product_metadata');
        $map = [];
        $filters = [];

        foreach ($attributes as $data) {
            $attr = $data['use'];
            $productsWithAttr = $products->whereNotNull($attr);

            if ($productsWithAttr->isNotEmpty()) {
                $map[$attr] = [];

                foreach ($productsWithAttr as $product) {
                    $attribute_value = $product->value($attr);

                    if (self::isArrayAndOneDeep($attribute_value)) {
                        foreach ($attribute_value as $arrayItem) {
                            $map[$attr][$arrayItem][] = $product->slug();
                        }
                    } elseif (is_string($attribute_value)) {
                        $map[$attr][$attribute_value][] = $product->slug();
                    }
                }

                $options = [];
                foreach ($map[$attr] as $key => $slugs) {
                    $options[] = [
                        'filter_id' => 'filters-'.$data['url'].'-'.self::encodeFilterOption($key),
                        'url' => $data['url'],
                        'name' => $key,
                        'checked' => false,
                        'value' => self::encodeFilterOption($key),
                        'model' => 'activeFilters.'.$data['url'].'.'.self::encodeFilterOption($key),
                        'product_count' => count($slugs),
                        '_products' => $slugs,
                    ];
                }

                $filters[] = [
                    'title' => $data['name'],
                    'options' => $options,
                ];
            }
        }

        return $filters;
    }

    /**
     * Generate a map of price filters and their corresponding products.
     *
     * @param  EntryCollection  $products  The collection of products to generate the map for.
     * @return array An associative array where the keys are the filter names
     *               and the values are arrays of filter options, each containing
     *               the filter name, value, model, product count, and an array of
     *               product slugs.
     */
    public static function generatePriceFilterMap(EntryCollection $products): array
    {
        $productPriceMap = [];

        $products->each(function ($product) use (&$productPriceMap) {
            $variants = Entry::query()
                ->where('site', Site::default()->handle())
                ->where('collection', 'variants')
                ->where('product_slug', $product->slug())->get();

            $minPrice = $variants->min(function ($variant) {
                return min($variant->price, $variant->compare_at_price ?: $variant->price);
            });
            $productPriceMap[$product->slug()] = $minPrice;
        });

        $minPrice = (float) collect($productPriceMap)->min();
        $maxPrice = (float) collect($productPriceMap)->max();

        $rangeSize = self::calculatePriceRangeSize($maxPrice);

        $result = [];

        for ($i = 0; $i <= $maxPrice; $i += $rangeSize) {
            $rangeStart = $i;
            $rangeEnd = $i + $rangeSize;

            $productsInThisRange = collect($productPriceMap)->filter(function ($price) use ($rangeStart, $rangeEnd) {
                return $price >= $rangeStart && $price < $rangeEnd;
            });

            if ($productsInThisRange->isNotEmpty()) {
                $value = floor($rangeStart).'-'.ceil($rangeEnd);

                $result[] = [
                    'filter_id' => 'price-'.$value,
                    'url' => 'price',
                    'value' => $value,
                    'model' => 'price.'.$value,
                    'name' => self::getPriceRangeName($rangeStart, $rangeEnd),
                    'checked' => false,
                    '_products' => $productsInThisRange->keys()->toArray(),
                    'product_count' => $productsInThisRange->count(),
                ];
            }
        }

        return [[
            'title' => 'Price',
            'options' => $result,
        ]];
    }

    /**
     * Returns true if the given array is an array and only one level deep.
     */
    public static function isArrayAndOneDeep(array $array): bool
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Encode a string to use as a filter option. This replaces spaces with
     * underscores and lowercases the string.
     */
    public static function encodeFilterOption(string $string): string
    {
        return Str::of(Str::replace(' ', '_', $string))->lower()->toString();
    }

    /**
     * Returns the filter config associated with the given URL key.
     *
     * @param  string  $urlKey  The URL key to search for.
     * @return array|null The filter config if found, otherwise null.
     */
    public static function getOptionFromConfig(string $urlKey): ?array
    {
        $filters = collect(config('gaia.filtering.filters'));

        return $filters->firstWhere('url', $urlKey);
    }

    /**
     * Retrieves the product slugs associated with a specific filter option from the filter map.
     *
     * @param  string  $url  The URL key of the filter option.
     * @param  string  $value  The value associated with the filter option.
     * @param  mixed  $map  The filter map containing filter options and their associated products.
     * @return array|null An array of product slugs if the filter option is found, otherwise null.
     */
    public static function getProductSlugsFromFilterMapOption(string $url, string $value, $map): ?array
    {
        $options = collect($map)->pluck('options')->collapse();

        $match = $options->first(function ($option) use ($url, $value) {
            return $option['url'] === $url && $option['value'] == $value;
        });

        return $match['_products'] ? $match['_products'] : null;
    }

    /**
     * Retrieves a list of product slugs from active filters excluding a specified filter.
     *
     * This method processes a collection of active filters and extracts product slugs
     * associated with each filter option, excluding the filter option identified by the given URL.
     * It ensures that only unique slugs from intersecting filter options are included in the result.
     *
     * @param  string  $url  The URL of the filter to exclude from the process.
     * @param  array  $filterMap  The map of filters containing their options.
     * @param  array  $activeFilters  An array of currently active filters and their options.
     * @return array An array of unique product slugs derived from the active filters, excluding the specified one.
     */
    public static function getActiveFilterSlugsFromAllFiltersButThisOne(string $url, array $filterMap, array $activeFilters): array
    {

        // Gets the slugs from every selected filter except from the same option (style, color).
        // We want the count of [color:red] on its own. Not the count of products containing
        // [colour:red] that also have the attribute [colour:blue]

        $options = collect($filterMap)
            ->pluck('options')
            ->collapse()
            ->reject(fn ($item) => $item['url'] === $url)
            ->values();

        $slugs = collect();

        foreach ($activeFilters as $filterUrl => $filterData) {
            if ($filterUrl == $url) {
                continue;
            }

            $currentSlugs = collect($filterData)
                ->keys()
                ->flatMap(function ($filterValue) use ($filterUrl, $options) {
                    $matchingOption = $options->first(fn ($item) => $item['url'] === $filterUrl && $item['value'] === $filterValue
                    );

                    return $matchingOption['_products'] ?? [];
                })
                ->unique();

            if ($slugs->isNotEmpty() && $currentSlugs->isNotEmpty()) {
                $slugs = $slugs->intersect($currentSlugs);
            } else {
                $slugs = $slugs->merge($currentSlugs);
            }
        }

        return $slugs->unique()->values()->toArray();
    }

    /**
     * Calculate the range size for a price filter given the maximum price.
     *
     * @param  float  $maxPrice  The maximum price.
     * @return float The range size.
     */
    private static function calculatePriceRangeSize(float $maxPrice): float
    {
        $maxPrice = max(0, $maxPrice);

        return match (true) {
            $maxPrice < 50 => 10,
            $maxPrice < 100 => 25,
            $maxPrice < 500 => 100,
            default => $maxPrice / 5,
        };
    }

    /**
     * Get a human-readable name for a price filter range.
     *
     * @param  float  $rangeStart  The start of the range.
     * @param  float  $rangeEnd  The end of the range.
     * @return string A human-readable representation of the price range.
     */
    private static function getPriceRangeName(float $rangeStart, float $rangeEnd): string
    {
        return Number::currency(
            $rangeStart,
            Site::current()->attribute('currency') ?? config('gaia.default_currency'),
            Site::current()->locale()
        ).' - '.Number::currency(
            $rangeEnd,
            Site::current()->attribute('currency') ?? config('gaia.default_currency'),
            Site::current()->locale()
        );
    }
}
