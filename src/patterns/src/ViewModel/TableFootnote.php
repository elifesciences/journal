<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class TableFootnote implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $text;
    private $footnoteId;
    private $footnoteLabel;

    public function __construct(string $text, string $id = null, string $label = null)
    {
        Assertion::notBlank($text);

        $this->text = $text;
        $this->footnoteId = $id;
        $this->footnoteLabel = $label;
    }
}
