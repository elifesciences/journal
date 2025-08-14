<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class SortControl implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $options;

    public function __construct(array $options)
    {
        Assertion::notEmpty($options);
        Assertion::allIsInstanceOf($options, SortControlOption::class);

        $this->options = $options;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/sort-control.mustache';
    }
}
