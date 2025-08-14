<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Orcid implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $id;

    public function __construct(string $id)
    {
        Assertion::regex($id, '/^([0-9]{4}-){3}[0-9]{3}[0-9X]$/');

        $this->id = $id;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/orcid.mustache';
    }
}
