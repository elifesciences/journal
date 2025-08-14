<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class MiniSection implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $body;
    private $listHeading;

    public function __construct(string $body, ListHeading $listHeading = null)
    {
        Assertion::notBlank($body);

        $this->body = $body;
        $this->listHeading = $listHeading;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/mini-section.mustache';
    }
}
