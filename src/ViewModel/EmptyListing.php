<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ListHeading;

final class EmptyListing implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $heading;
    private $text;

    public function __construct(ListHeading $heading = null, string $text)
    {
        $this->heading = $heading;
        $this->text = $text;
    }

    public function getTemplateName() : string
    {
        return 'empty-listing.mustache';
    }
}
