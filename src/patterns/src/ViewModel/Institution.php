<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class Institution implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
