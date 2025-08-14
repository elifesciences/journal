<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Highlight implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $heading;
    private $items;

    public function __construct(array $items, ListHeading $heading)
    {
        Assertion::notEmpty($items);
        Assertion::allIsInstanceOf($items, HighlightItem::class);

        $this->heading = $heading;
        $this->items = $items;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/highlight.mustache';
    }
}
