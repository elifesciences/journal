<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class CheckboxesOption implements CastsToArray, IsCheckboxesOption
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $value;
    private $label;
    private $id;
    private $checked;

    public function __construct(string $value, string $label, string $id = null, bool $checked = false)
    {
        Assertion::notBlank($value);
        Assertion::notBlank($label);
        Assertion::nullOrNotBlank($id);

        $this->value = $value;
        $this->label = $label;
        $this->id = $id;
        $this->checked = $checked;
    }
}
