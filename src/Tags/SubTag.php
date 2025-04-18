<?php

declare(strict_types=1);

namespace Arrowtide\Gaia\Tags;

use Statamic\Tags\Tags;

class SubTag extends Tags
{
    public function __construct(Gaia $tagData)
    {
        $this->content = $tagData->content;
        $this->context = $tagData->context;
        $this->params = $tagData->params;
        $this->tag = $tagData->tag;
        $this->method = $tagData->method;
        $this->parser = $tagData->parser;
    }
}
