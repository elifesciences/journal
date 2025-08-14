<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class SelectNav implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $route;
    private $select;
    private $button;

    public function __construct(string $route, Select $select, Button $button)
    {
        Assertion::notBlank($route);

        $this->route = $route;
        $this->select = $select;
        $this->button = $button;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/select-nav.mustache';
    }
}
