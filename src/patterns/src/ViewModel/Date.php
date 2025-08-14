<?php

namespace eLife\Patterns\ViewModel;

use DateTimeImmutable;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Date implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $isExpanded;
    private $isUpdated;
    private $forHuman;
    private $forMachine;

    private function __construct(DateTimeImmutable $date, bool $isExpanded = false, bool $isUpdated = false)
    {
        $this->isExpanded = $isExpanded;
        $this->isUpdated = $isUpdated;
        $this->forHuman = [
            'dayOfMonth' => (int) $date->format('j'),
            'month' => $date->format('M'),
            'year' => (int) $date->format('Y'),
        ];
        $this->forMachine = $date->format('Y-m-d');
    }

    public static function simple(DateTimeImmutable $date, bool $isUpdated = false) : Date
    {
        return new self($date, false, $isUpdated);
    }

    public static function expanded(DateTimeImmutable $date) : Date
    {
        return new self($date, true);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/date.mustache';
    }
}
