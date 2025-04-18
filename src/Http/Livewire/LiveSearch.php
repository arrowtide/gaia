<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Http\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Statamic\Facades\Search as StatamicSearch;
use Statamic\Facades\Site;

class LiveSearch extends Component
{
    public string $search = '';

    public function render()
    {
        return view('features/livesearch/_search', [
            'results' => $this->updatedSearch(),
            'livesearch_characters_left' => $this->getCharactersLeft(),
        ]);
    }

    public function updatedSearch(): array
    {
        if (Str::length($this->search) < config('gaia.search.min_characters')) {
            return [
                'shop' => [],
                'products' => [],
            ];
        }

        return [
            'shop' => $this->searchIndex('shop'),
            'products' => $this->searchIndex('products'),
        ];
    }

    private function searchIndex(string $collection)
    {

        $search = StatamicSearch::index('live-search')
            ->ensureExists()
            ->search($this->search)
            ->where('site', Site::current()->handle())
            ->where('collection', $collection)
            ->get()
            ->map(function ($item) {
                return $item->toAugmentedCollection();
            });

        return [
            'results' => $search,
            'count' => count($search),
            'no_results' => count($search) == 0,
        ];
    }

    private function getCharactersLeft()
    {
        if (Str::length($this->search) >= config('gaia.search.min_characters')) {
            return false;
        } else {
            return config('gaia.search.min_characters') - Str::length($this->search);
        }
    }
}
