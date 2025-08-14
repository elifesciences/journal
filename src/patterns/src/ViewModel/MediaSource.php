<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class MediaSource implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $src;
    private $mediaType;
    private $fallback;

    public function __construct(string $src, MediaType $mediaType, MediaSourceFallback $fallback = null)
    {
        Assertion::notBlank($src);

        $this->src = $src;
        $this->mediaType = $mediaType;
        $this->fallback = $fallback;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/media-source.mustache';
    }
}
