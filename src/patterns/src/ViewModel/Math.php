<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Math implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $math;
    private $id;
    private $label;

    public function __construct(string $math, string $id = null, string $label = null)
    {
        Assertion::regex($math, '/^<math>[\s\S]+<\/math>$/');

        $this->math = $math;
        $this->id = $id;
        $this->label = $label;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/math.mustache';
    }
}
