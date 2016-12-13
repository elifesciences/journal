<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\SimplifyAssets;
use eLife\Patterns\ViewModel;

final class Paragraph implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;
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
