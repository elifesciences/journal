<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ButtonCollection implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $buttons;
    private $inline;
    private $centered;
    private $compact;

    public function __construct(array $buttons, bool $inline = false, bool $centered = false, bool $compact = false)
    {
        Assertion::notEmpty($buttons);
        Assertion::allIsInstanceOf($buttons, Button::class);

        $this->buttons = $buttons;
        if ($inline) {
            $this->inline = $inline;
        }
        if ($centered) {
            $this->centered = $centered;
        }
        if ($compact) {
            $this->compact = $compact;
        }
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/button-collection.mustache';
    }
}
