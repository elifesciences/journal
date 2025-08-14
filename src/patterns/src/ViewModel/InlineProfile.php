<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class InlineProfile implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $image;
    private $text;

    public function __construct(Picture $image, string $text)
    {
        Assertion::notBlank($text);

        $this->image = $image;
        $this->text = $text;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/inline-profile.mustache';
    }
}
