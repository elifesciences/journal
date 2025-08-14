<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Paragraph implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $text;

    public function __construct(string $text)
    {
        Assertion::notBlank($text);

        $this->text = $text;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/paragraph.mustache';
    }
}
