<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Term implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $value;
    private $isHighlighted;

    public function __construct(string $value, bool $isHighlighted = false)
    {
        Assertion::notBlank($value);

        $this->value = $value;
        $this->isHighlighted = $isHighlighted;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/term.mustache';
    }
}
