<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class MessageGroup implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $id;
    private $errorText;
    private $infoText;

    private function __construct(
        string $errorText = null,
        string $infoText = null
    ) {
        $this->id = 'messages_'.random_int(10e4, 10e8);
        $this->errorText = $errorText;
        $this->infoText = $infoText;
    }

    public static function forInfoText(string $infoText, string $errorText = null) : MessageGroup
    {
        Assertion::notBlank($infoText);

        return new self($errorText, $infoText);
    }

    public static function forErrorText(string $errorText) : MessageGroup
    {
        Assertion::notBlank($errorText);

        return new self($errorText);
    }
}
