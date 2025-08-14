<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Select implements ViewModel
{
    const STATE_INVALID = 'invalid';
    const STATE_VALID = 'valid';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $id;
    private $options;
    private $label;
    private $name;
    private $required;
    private $disabled;
    private $state;
    private $messageGroup;

    public function __construct(
        string $id,
        array $options,
        FormLabel $label,
        string $name,
        bool $required = null,
        bool $disabled = null,
        string $state = null,
        MessageGroup $messageGroup = null
    ) {
        Assertion::notEmpty($options);
        Assertion::allIsInstanceOf($options, SelectOption::class);
        Assertion::nullOrChoice($state, [self::STATE_INVALID, self::STATE_VALID]);
        if (self::STATE_INVALID === $state) {
            Assertion::notNull($messageGroup);
            Assertion::notBlank($messageGroup['errorText']);
        }

        $this->id = $id;
        $this->options = $options;
        $this->label = $label;
        $this->name = $name;
        $this->required = $required;
        $this->disabled = $disabled;
        $this->state = $state;
        $this->messageGroup = $messageGroup;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/select.mustache';
    }
}
