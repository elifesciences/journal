<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class BlockLink implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $link;
    private $image;

    public function __construct(Link $link, Picture $image = null)
    {
        $this->link = $link;
        $this->image = $image;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/block-link.mustache';
    }
}
