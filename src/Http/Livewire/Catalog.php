<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Http\Livewire;

use Arrowtide\Gaia\Support\CatalogFilters as Filters;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Jonassiewertsen\Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
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
     * The active filter map, if there are any active filters.
     */
    public array $activeFilterMap = [];

    /**
     * The complete map of all filters. This is manipulated after we know what the
     * active filters are, so we can update the count on each filter.
     */
    public array $filterMap = [];

    /**
     * Called when a component is created.
     */
    public function mount($collections, Request $request): void
    {
        $this->collections = $collections;
        $this->currentUrl = $request->path();

        if (Filters::hasCachedMap($this->currentUrl)) {
            $this->filterMap = Filters::getCachedMap($this->currentUrl);
        } else {
            $this->filterMap = Filters::initMap()
                ->add(Filters::generatePriceFilterMap($this->products->get()))
                ->add(Filters::generateProductMetadataMap($this->products->get()))
                ->generate($this->currentUrl);
        }
    }

    /**
     * Called at the beginning of every request. Both initial, and subsequent
     */
    public function boot(): void
    {
        $this->products = $this->getProducts;
    }

    /**
     * Called after updating a property
     */
    public function updated(): void
    {
        $this->cleanFilters();
        $this->dispatch('scroll-to-top-of-listings');
    }

    /**
     * Renders the Livewire component.
     *
     * Returns a view with the current products, active filters, and a boolean
     * indicating whether there are any filters available.
     */
    public function render(): View
    {
        return view('shop.listings.default._listings', $this->fetchProducts(), [
            'filters' => $this->activeFilterMap,
            'active_filters' => $this->activeFilters,
            'no_filters' => empty($this->filterMap),
        ]);
    }

    /**
     * Sort the products by a given option.
     *
     * @param  string  $value  The sort option.
     */
    public function sort(string $value): void
    {
        $this->sortOption = $value;
    }

    /**
     * Update the page size.
     *
     * @param  int  $value  The new page size.
     */
    public function page(int $value): void
    {
        // Update the selected page size
        $this->pageSize = $value;
    }

    /**
     * The component to use for rendering the pagination links.
     */
    public function paginationView(): string
    {
        return 'shop.listings.default._pagination';
    }

    /**
     * Resets the active filters by clearing the active filters array
     * and the price range array.
     */
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
        // Extract sort field and direction from a selected sort option
        [$sortField, $sortDirection] = explode('_', $this->sortOption);

        if ($this->areAnyFiltersActive()) {
            // Iterates over the active filter options (color, size, etc.) value (red, green, blue, etc.),
            // replaces the values with slugs from the filter map, then filters the products by those slugs
            collect($this->allActiveFilters())->each(function ($filterData, $filterUrlKey) {
                $slugs = collect($filterData)->keys()->flatMap(function ($value) use ($filterUrlKey) {
                    return Filters::getProductSlugsFromFilterMapOption($filterUrlKey, $value, $this->filterMap);
                });

                if ($slugs->isNotEmpty()) {
                    $this->products = $this->products->whereIn('slug', $slugs->toArray());
                }
            });
        }

        if ($this->areAnyFiltersActive()) {
            $this->activeFilterMap = $this->getManipulatedFilterMap();
        } else {
            $this->activeFilterMap = $this->filterMap;
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

    /**
     * Sorts the products by price in the specified direction.
     *
     * @param  string  $sortDirection  The direction of the sort. Accepted values are 'asc' and 'desc'.
     * @return array The sorted products.
     */
    public function sortByPrice(string $sortDirection): array
    {
        $products = Filters::sortByPrice($this->products->get(), $sortDirection);

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

    /**
     * Gets the manipulated filter map.
     *
     * @return array The manipulated filter map.
     */
    private function getManipulatedFilterMap(): array
    {
        // Loop through each filter on the map and count how many matches there are for each filter option
        return collect($this->filterMap)->map(function ($filter) {
            $options = collect($filter['options'])->map(function ($option) {
                return collect($option)->when(isset($option['product_count']), function ($option) {

                    $count = count($option->get('_products'));
                    $isFilterOptionChecked = $this->isFilterOptionChecked($option->get('url'), $option->get('value'));

                    // Gets the slugs from every selected filter except from the same option (style, color).
                    // We want the count of [color:red] on its own. Not the count of products containing
                    // [colour:red] that also have the attribute [colour:blue]
                    $slugsFromOtherFiltersExceptThisOne = Filters::getActiveFilterSlugsFromAllFiltersButThisOne($option->get('url'), $this->filterMap, $this->allActiveFilters());

                    if (! empty($slugsFromOtherFiltersExceptThisOne)) {
                        $slugsFromOtherFiltersExceptThisOne = collect($slugsFromOtherFiltersExceptThisOne)->filter(function ($item) use ($option) {
                            return collect($option->get('_products'))->contains($item);
                        });

                        $count = $slugsFromOtherFiltersExceptThisOne->count();
                    }

                    // If the filter is the filter is checked, but the count is 0, then remove
                    // it from current filters. Prevents issues.
                    if ($isFilterOptionChecked && $count == 0) {
                        $this->removeFilterOption($option->get('url'), $option->get('value'));
                    }

                    $option['product_count'] = $count;
                    $option['checked'] = $isFilterOptionChecked;

                    return $option;
                })->all();
            });

            return [
                'title' => $filter['title'],
                'options' => $options->toArray(),
            ];
        })->toArray();
    }

    /**
     * Check if a filter option is checked.
     */
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

    /**
     * Removes a filter option from the active filters.
     *
     * @param  string  $url  The filter URL key.
     * @param  string  $value  The filter option value.
     */
    private function removeFilterOption(string $url, string $value): void
    {
        unset($this->activeFilters[$url][$value]);
    }

    /**
     * Cleans the active filters and price arrays by removing false values.
     *
     * This method uses a recursive function to remove false values from the active filters
     * and price arrays. If a filter option is set to false, it is removed from the active filters.
     * If a price range filter is set to false, it is removed from the price array.
     */
    private function cleanFilters(): void
    {
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

        $removeFalseValues($this->activeFilters);
        $removeFalseValues($this->price);
    }

    /**
     * Gets all current filters.
     *
     * This method returns a collection that contains all current filters,
     * including price filters.
     */
    private function allActiveFilters(): array
    {
        $allFilters = collect($this->activeFilters);

        if (! empty($this->price)) {
            $allFilters->put('price', $this->price);
        }

        return $allFilters->toArray();
    }

    /**
     * Checks if any filters are currently active.
     *
     * This method checks if any filters, including price filters, are currently active.
     *
     * @return bool True if any filters are currently active, false otherwise.
     */
    private function areAnyFiltersActive(): bool
    {
        return ! empty($this->allActiveFilters());
    }

    /**
     * Returns a query builder instance for the products that are visible in the catalog.
     *
     * This method takes into account the current collections passed through the Livewire component.
     */
    #[Computed(persist: true)]
    private function getProducts(): Builder
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
