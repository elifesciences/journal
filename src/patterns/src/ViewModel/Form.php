<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class Form implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $action;
    private $id;
    private $method;

    public function __construct(string $action, string $id, string $method)
    {
        Assertion::notBlank($action);
        Assertion::notBlank($id);
        Assertion::inArray($method, ['GET', 'POST']);

        $this->action = $action;
        $this->id = $id;
        $this->method = $method;
    }
}
