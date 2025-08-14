<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class FormFieldInfoLink implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $name;
    private $url;

    public function __construct(
        string $name,
        string $url
    ) {
        Assertion::notBlank($name);
        Assertion::notBlank($url);

        $this->name = $name;
        $this->url = $url;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/form-field-info-link.mustache';
    }
}
