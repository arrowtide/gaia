<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Actions;

class ParseMetafields
{
    /**
     * Parse any metafields
     *
     * @return array
     */
    public function execute(array $metafields, string $context)
    {
        if ($context === 'product-variant') {
            return $this->parseProductVariantMetafields($metafields);
        }

        if ($context === 'product') {
            return $this->parseProductMetafields($metafields);
        }

        return collect($metafields)->flatMap(fn ($field) => [
            $field['key'] => $field['value'],
        ])
            ->all();
    }

    private function parseProductVariantMetafields(array $metafields)
    {
        return collect($metafields)->flatMap(fn ($field) => [
            $field['key'] => $field['value'],
            'field_type' => $field['type'] ?? null,
            'description' => $field['description'] ?? null,
        ])
            ->all();
    }

    private function parseProductMetafields(array $metafields)
    {
        return collect($metafields)->flatMap(function ($field) {
            $value = $field['value'];

            // Check if the value is a JSON-encoded string
            if (is_string($value) && str_starts_with($value, '[') && str_ends_with($value, ']')) {
                // Decode the JSON string into an array
                $value = json_decode($value, true);
            }

            return [
                $field['key'] => $value,
            ];
        })->all();
    }
}
