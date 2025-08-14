<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ReadMoreItem implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $item;
    private $content;
    private $isRelated;

    public function __construct(
        ContentHeaderReadMore $item,
        string $content = null,
        bool $isRelated = false
    ) {
        $this->item = $item;
        $this->content = $content;
        $this->isRelated = $isRelated;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/read-more-item.mustache';
    }
}
