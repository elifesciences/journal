<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ReadOnlyArrayAccess;
use eLife\Patterns\SimplifyAssets;
use eLife\Patterns\ViewModel;

final class Paragraph implements ViewModel
{
    use ArrayFromProperties;
    use ReadOnlyArrayAccess;
    use SimplifyAssets;

    private $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function getTemplateName() : string
    {
        return '/elife/journal/patterns/paragraph.mustache';
    }
}
