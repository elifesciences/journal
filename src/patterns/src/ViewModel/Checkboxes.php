<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Checkboxes implements ViewModel
{
    const STATE_INVALID = 'invalid';
    const STATE_VALID = 'valid';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $id;
    private $checkboxes;
    private $name;
    private $title;
    private $required;
    private $disabled;
    private $state;
    private $messageGroup;

    public function __construct(
        string $id,
        array $checkboxes,
        string $name,
        string $title = null,
        bool $required = null,
        bool $disabled = null,
        string $state = null,
        MessageGroup $messageGroup = null
    ) {
        Assertion::notEmpty($checkboxes);
        Assertion::allIsInstanceOf($checkboxes, IsCheckboxesOption::class);
        Assertion::nullOrChoice($state, [self::STATE_INVALID, self::STATE_VALID]);
        if (self::STATE_INVALID === $state) {
            Assertion::notNull($messageGroup);
            Assertion::notBlank($messageGroup['errorText']);
        }
        Assertion::notBlank($name);

        $this->id = $id;
        $this->checkboxes = $checkboxes;
        $this->name = $name;
        $this->title = $title;
        $this->required = $required;
        $this->disabled = $disabled;
        $this->state = $state;
        $this->messageGroup = $messageGroup;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/checkboxes.mustache';
    }
}
