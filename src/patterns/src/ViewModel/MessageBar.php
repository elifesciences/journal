<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class MessageBar implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $message;

    public function __construct(string $message)
    {
        Assertion::notBlank($message);

        $this->message = $message;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/message-bar.mustache';
    }
}
