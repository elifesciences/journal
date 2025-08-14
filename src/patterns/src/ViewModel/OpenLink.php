<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class OpenLink implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $uri;
    private $width;
    private $height;

    public function __construct(string $uri, int $width, int $height)
    {
        Assertion::notBlank($uri);
        Assertion::min($width, 1);
        Assertion::min($height, 1);

        $this->uri = $uri;
        $this->width = $width;
        $this->height = $height;
    }
}
