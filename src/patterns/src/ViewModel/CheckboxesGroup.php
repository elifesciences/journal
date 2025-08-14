<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class CheckboxesGroup implements CastsToArray, IsCheckboxesOption
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $children;
    private $groupTitle;

    public function __construct(array $children, string $groupTitle = null)
    {
        Assertion::notEmpty($children);
        Assertion::allIsInstanceOf($children, CheckboxesOption::class);
        Assertion::nullOrNotBlank($groupTitle);

        $this->children = $children;
        $this->groupTitle = $groupTitle;
    }
}
