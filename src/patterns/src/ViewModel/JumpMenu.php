<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class JumpMenu implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $items;

    public function __construct(array $items)
    {
        Assertion::allIsInstanceOf($items, Link::class);
        if (count($items) > 0) {
            Assertion::min(count($items), 1);
        }

        $this->items = $items;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/jump-menu.mustache';
    }
}
