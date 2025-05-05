<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Tags;

use Statamic\Entries\EntryCollection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

class ProductTags extends SubTag
{
    private ?EntryCollection $cachedVariants = null;

    public function single(): bool
    {
        $entries = $this->getVariantEntries();

        return $entries->count() === 1;
    }

    public function configurable(): bool
    {
        $entries = $this->getVariantEntries();

        return $entries->count() > 1;
    }

    public function price(): ?array
    {
        $variants = Entry::query()
            ->where('site', Site::current()->handle())
            ->where('collection', 'variants')
            ->where('product_slug', $this->context->value('slug'))
            ->get();

        if ($variants->isEmpty()) {
            return null;
        }

        $priceMap = $variants->map(function ($variant) {
            return [
                'price' => $variant->price,
                'compare_at_price' => $variant->compare_at_price,
            ];
        });

        $minDisplayPrice = $priceMap->min('price');
        $maxDisplayPrice = $priceMap->max('price');
        $minCompareAtPrice = $priceMap->min('compare_at_price');
        $maxCompareAtPrice = $priceMap->max('compare_at_price');
        $isDiscounted = $minCompareAtPrice && ($minCompareAtPrice > $minDisplayPrice);

        $prices = [
            'min_display_price' => $minDisplayPrice,
            'max_display_price' => $maxDisplayPrice,
            'min_compare_at_price' => $minCompareAtPrice,
            'max_compare_at_price' => $maxCompareAtPrice,
            'is_discounted' => $isDiscounted
        ];

        $prices['is_uniform_price'] = $prices['min_display_price'] == $prices['max_display_price'];

        if ($isDiscounted) {
            $discountAmount = $minCompareAtPrice - $maxDisplayPrice;
            $prices['discount_amount'] = $discountAmount;
            $prices['discount_percentage'] = round(($discountAmount / $maxCompareAtPrice) * 100, 2);
        }

        return $prices;
    }

    public function options(): ?array
    {
        $entries = $this->getVariantEntries();

        if ($entries->count() === 1) {
            return null;
        }

        // Retrieve raw options data
        $optionsRaw = $this->context->get('options')->raw();

        // Determine option values or set them to null if not found
        $options = [];
        foreach (array_keys($optionsRaw) as $i => $optionName) {
            $optionValue = $optionsRaw && isset($optionsRaw[$optionName]) ? $optionsRaw[$optionName] : null;
            $uniqueOptionValues = $entries->pluck($optionName)->unique()->values()->all();

            $optionType = collect(config('gaia.variant_picker.custom'))->firstWhere('option', $optionValue)['type'] ?? config('gaia.variant_picker.default');

            $values = collect($uniqueOptionValues)->map(function ($value) use ($i, $optionValue) {

                $config = collect(config('gaia.variant_picker.custom'))->firstWhere('option', $optionValue);
                $data = [];

                $data[] = [
                    'value' => $value,
                ];

                if ($config && isset($config['metafields'])) {
                    foreach ($config['metafields'] as $metafield) {
                        $metafieldData = $this->getVariantEntries()->where('option'.$i + 1, $value)->first()->data()->get($metafield);
                        $data[] = [
                            $metafield => $metafieldData,
                        ];
                    }
                }

                return collect($data)->collapse()->toArray();
            });

            $options[] = [
                'name' => $optionValue,
                'type' => $optionType,
                'values' => $values->reverse()->values()->all(),
                'option' => $i + 1,
            ];
        }

        return $options;
    }

    private function getVariantEntries(): EntryCollection
    {
        if (! $this->cachedVariants) {
            $this->cachedVariants = Entry::query()
                ->where('site', Site::current()->handle())
                ->where('collection', 'variants')
                ->where('product_slug', $this->context->value('slug'))
                ->get();
        }

        return $this->cachedVariants;
    }
}
