<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ReadOnlyArrayAccess;
use eLife\Patterns\SimplifyAssets;
use eLife\Patterns\ViewModel;

final class Section implements ViewModel
{
    use ArrayFromProperties;
    use ReadOnlyArrayAccess;
    use SimplifyAssets;

    private $header;
    private $h;
    private $content;

    public function __construct(string $header, string $content, int $level = 1)
    {
        $this->header = $header;
        $this->h = $level + 1;
        $this->content = $content;
    }

    public function getTemplateName() : string
    {
        return '/elife/journal/patterns/section.mustache';
    }
}
