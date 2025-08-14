<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Listing implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $isOrdered;
    private $prefix;
    private $items;
    private $classes;

    private function __construct(bool $isOrdered, string $prefix = null, array $items, string $classes = null)
    {
        Assertion::nullOrChoice($prefix,
            ['alpha-lower', 'alpha-upper', 'bullet', 'number', 'roman-lower', 'roman-upper', 'line']);
        Assertion::notEmpty($items);
        Assertion::allString($items);

        $this->isOrdered = $isOrdered;
        $this->prefix = $prefix;
        $this->items = $items;
        $this->classes = $classes;
    }

    public static function ordered(array $items, string $prefix = null) : Listing
    {
        return new self(true, $prefix, $items);
    }

    public static function unordered(array $items, string $prefix = null) : Listing
    {
        return new self(false, $prefix, $items);
    }

    public static function forTeaser(array $items, string $prefix = 'bullet') : Listing
    {
        return new self(false, $prefix, $items, 'list--teaser');
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/list.mustache';
    }
}
