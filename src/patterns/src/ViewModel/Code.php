<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Code implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $code;

    public function __construct(string $code)
    {
        Assertion::notBlank($code);

        $this->code = $code;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/code.mustache';
    }
}
