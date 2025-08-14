<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class HiddenField implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $name;
    private $id;
    private $value;

    public function __construct(string $name = null, string $id = null, string $value = null)
    {
        $this->name = $name;
        $this->id = $id;
        $this->value = $value;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/hidden-field.mustache';
    }
}
