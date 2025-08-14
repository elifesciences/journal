<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class Profile implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $name;
    private $url;
    private $image;

    public function __construct(Link $link, Picture $image = null)
    {
        $this->name = $link['name'];
        $this->url = $link['url'];
        $this->image = $image;
    }
}
