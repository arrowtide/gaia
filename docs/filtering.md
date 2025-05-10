
## Filtering
Gaia lets you filter products by **product metadata** or **price** using Livewire. To get started, the filters you want to display should be added to `config/gaia.php`.

```php
'filters' => [
    [
        // The visual name of the filter to be shown as the title above the options
        'name' => 'Plant Type',
        
        // The key of the data you want to use. For this example there will be a 
        // plant_type: Succulent inside of the product data. 
        'use' => 'plant_type',

        // The type of filter. See above for options.
        'type' => 'product_metadata',

        // The url key of the filter, this should be unique across all filters
        // and is what will display in the url when you select options. 
        'url' => 'plant_type',
    ],
],
```

### Possible types
Here's a list of types you can currently filter by:

| Name | Key | Description | 
| --- | ----------- | --- |
| Product Metadata | product_metadata | Product metadata is data that is applied to the product (not variants).
