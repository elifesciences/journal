<?php

namespace eLife\Journal\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\SimplifyAssets;
use eLife\Patterns\ViewModel;

final class DefinitionList implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;
    use SimplifyAssets;

    private $items;

    public function __construct(array $items)
    {
        Assertion::notEmpty($items);

        $this->items = array_map(function (string $term, string $descriptor) {
            return compact('term', 'descriptor');
        }, array_keys($items), array_values($items));
    }

    public function getTemplateName() : string
    {
        return '/elife/journal/patterns/definition-list.mustache';
    }
}
