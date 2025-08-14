<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ModalWindow implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $smallDeviceOnly;
    private $title;
    private $body;
    private $closeBtnText;
    private $triggerId;

    private function __construct(
        bool $smallDeviceOnly,
        string $title,
        string $body,
        string $closeBtnText = null,
        string $triggerId = null
    )
    {
        Assertion::notBlank($title);
        Assertion::notBlank($body);
        Assertion::nullOrNotBlank($closeBtnText);
        Assertion::nullOrNotBlank($triggerId);

        $this->smallDeviceOnly = $smallDeviceOnly;
        $this->title = $title;
        $this->body = $body;
        $this->closeBtnText = $closeBtnText;
        $this->triggerId = $triggerId;
    }

    public static function create(
        string $title,
        string $body,
        string $closeBtnText = null,
        string $triggerId = null
    ) : ModalWindow
    {
        return new self(false, $title, $body, $closeBtnText, $triggerId);
    }

    public static function small(
        string $title,
        string $body,
        string $closeBtnText = null,
        string $triggerId = null
    ) : ModalWindow
    {
        return new self(true, $title, $body, $closeBtnText, $triggerId);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/modal-window.mustache';
    }
}
