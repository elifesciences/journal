<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class TextArea implements ViewModel
{
    const STATE_INVALID = 'invalid';
    const STATE_VALID = 'valid';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $label;
    private $name;
    private $id;
    private $value;
    private $placeholder;
    private $required;
    private $disabled;
    private $autofocus;
    private $cols;
    private $rows;
    private $form;
    private $state;
    private $messageGroup;

    public function __construct(
        FormLabel $label,
        string $id,
        string $name,
        string $value = null,
        string $placeholder = null,
        bool $required = null,
        bool $disabled = null,
        bool $autofocus = null,
        int $cols = null,
        int $rows = null,
        string $form = null,
        string $state = null,
        MessageGroup $messageGroup = null
    ) {
        Assertion::nullOrChoice($state, [self::STATE_INVALID, self::STATE_VALID]);

        if (self::STATE_INVALID === $state) {
            Assertion::notNull($messageGroup);
            Assertion::notBlank($messageGroup['errorText']);
        }
        $this->label = $label;
        $this->name = $name;
        $this->id = $id;
        $this->value = $value;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->disabled = $disabled;
        $this->autofocus = $autofocus;
        $this->cols = $cols;
        $this->rows = $rows;
        $this->form = $form;
        $this->state = $state;
        $this->messageGroup = $messageGroup;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/text-area.mustache';
    }
}
