<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class StatisticCollection implements ViewModel
{
    use ArrayFromProperties;
    use ArrayAccessFromProperties;

    private $stats;

    public function __construct(Statistic ...$stats)
    {
        Assertion::notEmpty($stats);

        $this->stats = $stats;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/statistic-collection.mustache';
    }
}
