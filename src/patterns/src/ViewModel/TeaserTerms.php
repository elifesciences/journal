<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class TeaserTerms implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $items;

    public function __construct(array $items)
    {
        Assertion::notEmpty($items);
        Assertion::allIsInstanceOf($items, Term::class);

        $this->items = $items;
    }
}
