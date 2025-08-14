<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class ContextLabel implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $list;

    public function __construct(Link ...$list)
    {
        Assertion::notEmpty($list);

        $this->list = $list;
    }
}
