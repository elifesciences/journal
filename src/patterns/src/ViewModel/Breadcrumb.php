<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Breadcrumb implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $items;

    public function __construct(array $items)
    {
        Assertion::notEmpty($items);
        Assertion::allIsInstanceOf($items, Link::class);

        $this->items = $items;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/breadcrumb.mustache';
    }
}
