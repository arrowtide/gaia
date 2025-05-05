<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Http\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use MarcoRieser\Livewire\WithPagination;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

class Catalog extends Component
{
    use WithPagination;

    #[Url]
    public array $activeFilters = [];

    #[Url]
    public ?string $sortOption = 'title_asc';

    #[Url]
    public ?int $pageSize = 12;

    #[Url]
    public ?array $price = [];

    /**
     * The tag should pass this data in as a parameter. Used to filter the products
     * down to the correct collections.
     */
    public array $collections = [];

    /**
     * The current product list, this is set every time during the booting process
     * of the component. Is further manipulated as filters are applied.
     */
    private $products = null;

    /**
     * The current URL of the page you're on. This is set during mount so that
     * the url isn't livewire/update after subsequent requests.
     */
    public string $currentUrl = '';

    /**
     * The complete map of all filters. This is manipulated after we know what the
     * active filters are, so we can update the count on each filter.
     */
    public array $filterMap = [];

    /**
     * Livewire mount
     */
    public function mount($collections, Request $request): void
    {
        $this->collections = $collections;
        $this->currentUrl = $request->path();
        $this->filterMap = $this->getFilterData();
    }

    /**
     * Livewire boot
     */
    public function boot(): void
    {
        $this->products = $this->getProducts;
    }

    /**
     * Livewire updated
     */
    public function updated(): void
    {
        $this->cleanFilters();
        $this->dispatch('scroll-to-top-of-listings');
    }

    /**
     * Livewire render
     */
    public function render()
    {
        return view('shop.listings.default._listings', $this->fetchProducts(), [
            'filters' => $this->getFilterMap(),
            'active_filters' => $this->activeFilters,
            'no_filters' => empty($this->getFilterMap()),
        ]);
    }

    public function sort($value): void
    {
        $this->sortOption = $value;
    }

    public function page($value): void
    {
        // Update selected page size
        $this->pageSize = $value;
    }

    public function paginationView()
    {
        return 'shop.listings.default._pagination';
    }

    public function resetFilters(): void
    {
        $this->activeFilters = [];
        $this->price = [];
    }

    /**
     * The primary function used to fetch and filter products
     */
    #[On('catalog-fetch-products')]
    public function fetchProducts(): array
    {
        // Extract sort field and direction from selected sort option
        [$sortField, $sortDirection] = explode('_', $this->sortOption);

        if ($this->areAnyFiltersActive()) {
            $this->applyFilters();
        }

        // Handle sorting based on the field
        if ($sortField === 'price') {
            return $this->sortByPrice($sortDirection);
        }

        $products = $this->products
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->pageSize);

        return $this->withPagination('products', $products);
    }

    private function applyFilters(): void
    {
        $filters = collect(config('gaia.filtering.filters'));

        $this->getAllCurrentFilters()->each(function ($filterData, $filterKey) use ($filters) {
            $filterConfig = $filters->firstWhere('url', $filterKey);

            if (! $filterConfig) {
                return;
            }

            $use = $filterConfig['use'];
            $type = $filterConfig['type'];

            if (in_array($type, ['product_metadata', 'price'])) {
                $slugs = collect($filterData)->keys()->flatMap(function ($option) use ($use) {
                    return $this->getSlugsFromFilterMap($use, $option);
                });

                if ($slugs->isNotEmpty()) {
                    $this->products = $this->products->whereIn('slug', $slugs->toArray());
                }
            }
        });
    }

    public function sortByPrice($sortDirection): array
    {
        $products = $this->products
            ->get()
            ->map(function ($product) {
                $minPrice = $this->getProductMinPrice($product);
                $product->min_price = $minPrice;

                return $product;
            })
            ->sortBy('min_price', SORT_REGULAR, $sortDirection === 'desc')
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $products->slice(($currentPage - 1) * $this->pageSize, $this->pageSize)->all();

        $paginator = new LengthAwarePaginator(
            $currentItems,
            $products->count(),
            $this->pageSize,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return $this->withPagination('products', $paginator);
    }

    private function getProductMinPrice($product): float
    {
        $variants = $this->getProductVariants()->where('product_slug', $product->slug())->get();

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

    /**
     * Gets the raw data for the filters, before any filtering has taken place.
     */
    public function getFilterData()
    {
        $cache_key = 'gaia/filters/'.$this->currentUrl;
        $filters = [];

        if (Cache::has($cache_key) && config('gaia.filtering.cache_enabled')) {
            return json_decode(Cache::get($cache_key), true);
        } else {
            $filters = array_merge(
                $filters,
                $this->getPriceFilter(),
                $this->getProductMetadataFilters(),
            );

            if (config('gaia.filtering.cache_enabled')) {
                $filtersJSON = json_encode($filters);
                Cache::put($cache_key, $filtersJSON, config('gaia.filtering.cache_duration'));
            }

            return $filters;
        }
    }

    private function getFilterMap(): array
    {
        return $this->areAnyFiltersActive() ? $this->getManipulatedFilterMap() : $this->filterMap;
    }

    private function getManipulatedFilterMap(): array
    {
        // Loop through each filter on the map and count how many matches there are for each filter option
        $modifiedFilterMap = collect($this->filterMap)->map(function ($filter) {

            $options = collect($filter['options'])->map(function ($option) {
                return collect($option)->when(isset($option['product_count']), function ($option) {

                    $count = count($option->get('_products'));
                    $isFilterOptionChecked = $this->isFilterOptionChecked($option->get('url'), $option->get('value'));

                    /**
                     * Gets the slugs from every selected filter except from the same option (style, colour). We want the count of Colour:red
                     * on its own. Not the count of products containing colour:red that also have the attribute colour:blue
                     */
                    $slugsFromOtherFiltersExceptThisOne = $this->getActiveFilterSlugsFromEverythingButTheSameFilter($option->get('url'), $option->get('value'));

                    if (! empty($slugsFromOtherFiltersExceptThisOne)) {
                        $slugsFromOtherFiltersExceptThisOne = collect($slugsFromOtherFiltersExceptThisOne)->filter(function ($item) use ($option) {
                            return collect($option->get('_products'))->contains($item);
                        });

                        $count = $slugsFromOtherFiltersExceptThisOne->count();
                    }

                    /**
                     * If the filter Is the filter is checked, but the count is 0, then remove it from current filters. Prevents issues.
                     */
                    if ($isFilterOptionChecked && $count == 0) {
                        $this->removeFilterOption($option->get('url'), $option->get('value'));
                    }

                    $option['product_count'] = $count;
                    $option['checked'] = $isFilterOptionChecked;

                    return $option;
                })->all();
            });

            return [
                'id' => $filter['id'],
                'title' => $filter['title'],
                'options' => $options->toArray(),
            ];
        });

        return $modifiedFilterMap->toArray();
    }

    private function isFilterOptionChecked(string $url, string $value): bool
    {
        // Check in regular filters
        if (isset($this->activeFilters[$url][$value])) {
            return true;
        }

        // Check in price range filters
        if (isset($this->price[$value])) {
            return true;
        }

        return false;
    }

    private function removeFilterOption($url, $value): void
    {
        unset($this->activeFilters[$url][$value]);
    }

    private function getActiveFilterSlugsFromEverythingButTheSameFilter($url, $value): array
    {
        $options = collect($this->filterMap)
            ->pluck('options')
            ->collapse()
            ->reject(fn ($item) => $item['url'] === $url)
            ->values();

        $slugs = collect();

        foreach ($this->getAllCurrentFilters() as $filterUrl => $filterData) {
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

    private function getPriceFilter(): array
    {
        $productPriceMap = [];

        $this->products->get()->each(function ($product) use (&$productPriceMap) {
            $variants = $this->getProductVariants()->where('product_slug', $product->slug())->get();
            $minPrice = $variants->min(function ($variant) {
                return min($variant->price, $variant->compare_at_price ?: $variant->price);
            });
            $productPriceMap[$product->slug()] = $minPrice;
        });

        $minPrice = (float) collect($productPriceMap)->min();
        $maxPrice = (float) collect($productPriceMap)->max();

        $rangeSize = $this->calculateRangeSize($maxPrice);

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
                    'name' => $this->getRangeName($rangeStart, $rangeEnd),
                    'checked' => false,
                    '_products' => $productsInThisRange->keys()->toArray(),
                    'product_count' => $productsInThisRange->count(),
                ];
            }
        }

        return [[
            'id' => Str::lower(Str::random(10)),
            'title' => 'Price',
            'options' => $result,
        ]];
    }

    private function calculateRangeSize(float $maxPrice): float
    {
        $maxPrice = max(0, $maxPrice);

        return match (true) {
            $maxPrice < 50 => 10,
            $maxPrice < 100 => 25,
            $maxPrice < 500 => 100,
            default => $maxPrice / 5,
        };
    }

    private function getRangeName($rangeStart, $rangeEnd): string
    {
        return Number::currency(
            (float) $rangeStart,
            Site::current()->attribute('currency') ?? config('gaia.default_currency'),
            Site::current()->locale()
        ).' - '.Number::currency(
            (float) $rangeEnd,
            Site::current()->attribute('currency') ?? config('gaia.default_currency'),
            Site::current()->locale()
        );
    }

    private function getProductMetadataFilters(): array
    {
        $attributes = collect(config('gaia.filtering.filters'))->where('type', 'product_metadata');
        $map = [];
        $filters = [];

        $allProducts = $this->products->get();

        foreach ($attributes as $data) {
            $attr = $data['use'];
            $productsWithAttr = $allProducts->whereNotNull($attr);

            if ($productsWithAttr->isNotEmpty()) {
                $map[$attr] = [];

                foreach ($productsWithAttr as $product) {
                    $attribute_value = $product->value($attr);

                    if ($this->isArrayAndOneDeep($attribute_value)) {
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
                        'filter_id' => 'filters-'.$data['url'].'-'.$this->encodeFilterOption($key),
                        'url' => $data['url'],
                        'name' => $key,
                        'checked' => false,
                        'value' => $this->encodeFilterOption($key),
                        'model' => 'activeFilters.'.$data['url'].'.'.$this->encodeFilterOption($key),
                        'product_count' => count($slugs),
                        '_products' => $slugs,
                    ];
                }

                $filters[] = [
                    'id' => Str::lower(Str::random(10)),
                    'title' => $data['name'],
                    'options' => $options,
                ];
            }
        }

        return $filters;
    }

    private function cleanFilters(): void
    {
        // Function to remove false values recursively
        $removeFalseValues = function (&$array) use (&$removeFalseValues) {
            foreach ($array as $key => &$value) {
                if (is_array($value)) {
                    $removeFalseValues($value);
                    if (empty(array_filter($value))) {
                        unset($array[$key]);
                    }
                } elseif ($value === false) {
                    unset($array[$key]);
                }
            }
        };

        // Clean filters and price arrays by removing false values
        $removeFalseValues($this->activeFilters);
        $removeFalseValues($this->price);
    }

    private function getAllCurrentFilters(): Collection
    {
        $allFilters = collect($this->activeFilters);

        if (! empty($this->price)) {
            $allFilters->put('price', $this->price);
        }

        return $allFilters;
    }

    private function getSlugsFromFilterMap($filterName, $filterValue)
    {
        $options = collect($this->filterMap)->pluck('options')->collapse();

        $match = $options->first(function ($option) use ($filterName, $filterValue) {
            return $option['url'] === $filterName && $option['value'] == $filterValue;
        });

        return $match ? $match['_products'] : null;
    }

    private function isArrayAndOneDeep($array): bool
    {
        if (! is_array($array)) {
            return false;
        }

        foreach ($array as $value) {
            if (is_array($value)) {
                return false;
            }
        }

        return true;
    }

    private function areAnyFiltersActive(): bool
    {
        return ! empty($this->activeFilters) || ! empty($this->price);
    }

    private function encodeFilterOption($string): string
    {
        return Str::of(Str::replace(' ', '_', $string))->lower()->toString();
    }

    #[Computed(persist: true)]
    private function getProductVariants()
    {
        return Entry::query()->where('site', Site::default()->handle())->where('collection', 'variants');
    }

    #[Computed(persist: true)]
    private function getProducts()
    {
        $products = Entry::query()
            ->where('site', Site::current()->handle())
            ->where('collection', 'products');

        if (! empty($this->collections)) {
            $products = $products->where(function ($query) {
                foreach ($this->collections as $collection) {
                    $query->orWhereJsonContains('collections', $collection);
                }
            });
        }

        return $products;
    }
}
