<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Tags;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UtilTags extends SubTag
{
    public function exception()
    {
        $message = $this->params->get('message');

        throw new \Exception($message);
    }

    public function spread()
    {
        $paramString = collect($this->params->get('replace_with'))->map(function ($value, $key) {
            return "{$key}=\"{$value}\"";
        })->implode(' ');

        $this->content = Str::replace("...{$this->params->get('replace')}", $paramString, $this->content);

        return $this->parse();
    }

    public function attr(): string|bool
    {
        if (! $this->params->get('get') || ! isset($this->context->get('view')[$this->params->get('get')])) {
            return false;
        }

        return $this->context->get('view')[$this->params->get('get')];
    }

    public function attrs($wildcard): array|string
    {

        if (! $wildcard) {
            return '';
        }

        $namePrefix = "{$wildcard}:";

        $ignore = $this->params->get('not')
            ? collect(explode('|', $this->params->get('not')))
            : false;

        $attrs = collect($this->context->get('view'))
            ->filter(function ($value, $key) use ($namePrefix) {
                return Str::startsWith($key, $namePrefix);
            })
            ->mapWithKeys(function ($value, $key) use ($namePrefix) {
                $newKey = Str::replaceFirst($namePrefix, '', $key);

                return [$newKey => $value];
            });

        // Remove unwanted parameters with the `not` param
        if ($ignore) {
            $attrs = $attrs->filter(function ($value) use ($ignore) {
                return $ignore->doesntContain($value);
            });
        }

        if ($this->params->get('format') == 'array') {
            return $attrs->all();
        }

        return collect($attrs)->map(function ($value, $key) {
            return "{$key}=\"{$value}\"";
        })->implode(' ');
    }

    public function imageComponentSizes()
    {
        $screens = config('gaia.screens');

        $images = collect($this->context->get('view'))
            ->filter(function ($value, $key) {
                return Str::startsWith($key, 'sizes:');
            })
            ->mapWithKeys(function ($value, $key) use ($screens) {

                $query = Str::after($key, ':');

                if (Arr::has($screens, $query)) {
                    $query = $screens[$query]; // Get the corresponding value
                }

                return [$query => [
                    'width' => Str::match('/\d+/', Str::before($value, ',')),
                    'height' => Str::match('/\d+/', Str::after($value, ',')),
                    'query' => $query,
                ]];
            });

        return $images->sortKeysDesc()->all();
    }
}
