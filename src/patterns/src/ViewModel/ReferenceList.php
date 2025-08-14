<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ReferenceList implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $references;

    public function __construct(Reference ...$references)
    {
        Assertion::notEmpty($references);

        $this->references = $references;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/reference-list.mustache';
    }
}
