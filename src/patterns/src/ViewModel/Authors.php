<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Authors implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $list;
    private $institutions;

    public function __construct(
        array $list,
        array $institutions = []
    ) {
        Assertion::notEmpty($list);
        Assertion::allIsInstanceOf($list, Author::class);
        Assertion::allIsInstanceOf($institutions, Institution::class);
        $this->list = $list;
        if ($institutions) {
            $this->institutions = ['list' => $institutions];
        }
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/authors.mustache';
    }
}
