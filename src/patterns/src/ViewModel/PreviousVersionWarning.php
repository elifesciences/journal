<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class PreviousVersionWarning implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $text;
    private $link;

    public function __construct(string $text, Link $link = null)
    {
        Assertion::notBlank($text);

        $this->text = $text;
        $this->link = $link;
    }
}
