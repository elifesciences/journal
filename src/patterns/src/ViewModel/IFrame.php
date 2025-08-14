<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class IFrame implements ViewModel, IsCaptioned
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $src;
    private $title;
    private $allowFullScreen;
    private $paddingBottom;

    public function __construct(string $src, int $width, int $height, string $title = null, bool $allowFullScreen = true)
    {
        Assertion::notBlank($src);
        Assertion::min($width, 1);
        Assertion::min($height, 1);

        $this->src = $src;
        $this->title = $title;
        $this->paddingBottom = ($height / $width) * 100;
        $this->allowFullScreen = $allowFullScreen;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/iframe.mustache';
    }
}
