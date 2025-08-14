<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Statistic implements ViewModel
{
    use ArrayFromProperties;
    use ArrayAccessFromProperties;

    private $label;
    private $value;
    private $shouldNotEscapeTerm;

    public static function fromString(string $label, string $value, string $shouldNotEscapeTerm = null)
    {
        return new static($label, $value, $shouldNotEscapeTerm);
    }

    public static function fromNumber(string $label, int $value, string $shouldNotEscapeTerm = null)
    {
        return new static($label, number_format($value), $shouldNotEscapeTerm);
    }

    private function __construct(string $label, string $value, string $shouldNotEscapeTerm = null)
    {
        Assertion::notBlank($label);
        Assertion::notBlank($value);

        $this->label = $label;
        $this->value = $value;
        $this->shouldNotEscapeTerm = $shouldNotEscapeTerm;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/statistic.mustache';
    }
}
