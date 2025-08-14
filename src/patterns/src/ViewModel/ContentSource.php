<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class ContentSource implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $contentType;
    private $text;

    public function __construct(Link $contentType, string $text = null)
    {
        $this->contentType = $contentType;
        $this->text = $text;
    }
}
