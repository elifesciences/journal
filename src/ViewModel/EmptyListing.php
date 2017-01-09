<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\SimplifyAssets;
use eLife\Patterns\ViewModel;

final class EmptyListing implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;
    use SimplifyAssets;

    private $heading;
    private $text;

    public function __construct(string $heading, string $text)
    {
        $this->heading = $heading;
        $this->text = $text;
    }

    public function getTemplateName() : string
    {
        return '/elife/journal/patterns/empty-listing.mustache';
    }
}
