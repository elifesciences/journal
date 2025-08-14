<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class FilterPanel implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $title;
    private $filterGroups;
    private $button;

    public function __construct(
        string $title,
        array $filterGroups,
        Button $button
    ) {
        Assertion::notBlank($title);
        Assertion::notEmpty($filterGroups);
        Assertion::allIsInstanceOf($filterGroups, FilterGroup::class);

        $this->title = $title;
        $this->filterGroups = $filterGroups;
        $this->button = $button;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/filter-panel.mustache';
    }
}
