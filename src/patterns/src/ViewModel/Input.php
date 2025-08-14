<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class Input implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $label;
    private $type;
    private $name;
    private $value;
    private $placeholder;
    private $autofocus;

    public function __construct(
        string $label,
        string $type,
        string $name,
        string $value = null,
        string $placeholder = null,
        bool $autofocus = false
    ) {
        Assertion::notBlank($label);
        Assertion::inArray($type, ['email', 'password', 'search', 'tel', 'text', 'url']);
        Assertion::notBlank($name);

        $this->label = $label;
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
        $this->placeholder = $placeholder;
        if ($autofocus) {
            $this->autofocus = $autofocus;
        }
    }
}
