<?php

namespace eLife\Journal\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\SimplifyAssets;
use eLife\Patterns\ViewModel;

final class HiddenInput implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;
    use SimplifyAssets;

    private $name;
    private $id;
    private $value;

    public function __construct(string $name, string $id, string $value = '')
    {
        Assertion::notBlank($name);
        Assertion::notBlank($id);

        $this->name = $name;
        $this->id = $id;
        $this->value = $value;
    }

    public function getTemplateName() : string
    {
        return 'hidden-input.mustache';
    }
}
