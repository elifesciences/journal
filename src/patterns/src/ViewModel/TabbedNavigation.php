<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class TabbedNavigation implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $items;

    public function __construct(array $items = null) {
        Assertion::nullOrNotEmpty($items);
        if (null !== $items) {
            Assertion::allIsInstanceOf($items, TabbedNavigationLink::class);
        }

        $this->items = $items;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/tabbed-navigation.mustache';
    }
}
