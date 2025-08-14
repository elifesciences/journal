<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class TextField implements ViewModel
{
    const STATE_INVALID = 'invalid';
    const STATE_VALID = 'valid';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $inputType;
    private $label;
    private $name;
    private $id;
    private $placeholder;
    private $required;
    private $disabled;
    private $isHiddenUntilChecked;
    private $checkboxId;
    private $autofocus;
    private $value;
    private $state;
    private $messageGroup;
    private $formFieldInfoLink;

    protected function __construct(
        string $inputType,
        FormLabel $label,
        string $id,
        string $name,
        string $placeholder = null,
        bool $required = null,
        bool $disabled = null,
        bool $isHiddenUntilChecked = null,
        string $checkboxId = null,
        bool $autofocus = null,
        string $value = null,
        string $state = null,
        MessageGroup $messageGroup = null,
        FormFieldInfoLink $formFieldInfoLink = null
    ) {
        Assertion::notBlank($inputType);
        Assertion::inArray($inputType, ['email', 'password', 'search', 'tel', 'text', 'url']);
        Assertion::nullOrChoice($state, [self::STATE_INVALID, self::STATE_VALID]);

        if (self::STATE_INVALID === $state) {
            Assertion::notNull($messageGroup);
            Assertion::notBlank($messageGroup['errorText']);
        }
        $this->inputType = $inputType;
        $this->label = $label;
        $this->name = $name;
        $this->id = $id;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->disabled = $disabled;
        $this->isHiddenUntilChecked = $isHiddenUntilChecked;
        $this->checkboxId = $checkboxId;
        $this->autofocus = $autofocus;
        $this->value = $value;
        $this->state = $state;
        $this->messageGroup = $messageGroup;
        $this->formFieldInfoLink = $formFieldInfoLink;
    }

    public static function emailInput(
        FormLabel $label,
        string $id,
        string $name,
        FormFieldInfoLink $formFieldInfoLink = null,
        string $placeholder = null,
        bool $required = null,
        bool $disabled = null,
        bool $autofocus = null,
        string $value = null,
        string $state = null,
        MessageGroup $messageGroup = null
    ) {
        return new static('email', $label, $id, $name, $placeholder, $required, $disabled, null, null, $autofocus, $value, $state, $messageGroup, $formFieldInfoLink);
    }

    public static function passwordInput(
        FormLabel $label,
        string $id,
        string $name,
        string $placeholder = null,
        bool $required = null,
        bool $disabled = null,
        bool $autofocus = null,
        string $value = null,
        string $state = null,
        MessageGroup $messageGroup = null
    ) {
        return new static('password', $label, $id, $name, $placeholder, $required, $disabled, null, null, $autofocus, $value, $state, $messageGroup);
    }

    public static function searchInput(
        FormLabel $label,
        string $id,
        string $name,
        string $placeholder = null,
        bool $required = null,
        bool $disabled = null,
        bool $autofocus = null,
        string $value = null,
        string $state = null,
        MessageGroup $messageGroup = null
    ) {
        return new static('search', $label, $id, $name, $placeholder, $required, $disabled, null, null, $autofocus, $value, $state, $messageGroup);
    }

    public static function telInput(
        FormLabel $label,
        string $id,
        string $name,
        string $placeholder = null,
        bool $required = null,
        bool $disabled = null,
        bool $autofocus = null,
        string $value = null,
        string $state = null,
        MessageGroup $messageGroup = null
    ) {
        return new static('tel', $label, $id, $name, $placeholder, $required, $disabled, null, null, $autofocus, $value, $state, $messageGroup);
    }

    public static function textInput(
        FormLabel $label,
        string $id,
        string $name,
        string $placeholder = null,
        bool $required = null,
        bool $disabled = null,
        bool $isHiddenUntilChecked = null,
        string $checkboxId = null,
        bool $autofocus = null,
        string $value = null,
        string $state = null,
        MessageGroup $messageGroup = null
    ) {
        return new static('text', $label, $id, $name, $placeholder, $required, $disabled, $isHiddenUntilChecked, $checkboxId, $autofocus, $value, $state, $messageGroup);
    }

    public static function urlInput(
        FormLabel $label,
        string $id,
        string $name,
        string $placeholder = null,
        bool $required = null,
        bool $disabled = null,
        bool $autofocus = null,
        string $value = null,
        string $state = null,
        MessageGroup $messageGroup = null
    ) {
        return new static('url', $label, $id, $name, $placeholder, $required, $disabled, null, null, $autofocus, $value, $state, $messageGroup);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/text-field.mustache';
    }
}
