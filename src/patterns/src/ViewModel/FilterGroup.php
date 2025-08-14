<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class FilterGroup implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $filterTitle;
    private $filters;
    private $selectFilterName;

    public function __construct(string $filterTitle = null, array $filters, string $selectFilterName = null)
    {
        Assertion::nullOrNotBlank($filterTitle);
        Assertion::allIsInstanceOf($filters, Filter::class);
        Assertion::nullOrNotBlank($selectFilterName);

        $this->filterTitle = $filterTitle;
        $this->filters = $filters;
        $this->selectFilterName = $selectFilterName;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/filter-group.mustache';
    }
}
