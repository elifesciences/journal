<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ImageLink implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $url;
    private $image;

    public function __construct(string $url, Picture $image)
    {
        $this->url = $url;
        $this->image = $image;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/image-link.mustache';
    }
}
