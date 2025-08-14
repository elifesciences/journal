<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class HighlightItem implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $subjects;
    private $link;
    private $summary;
    private $authors;
    private $button;
    private $meta;
    private $image;

    public function __construct(array $subjects, Link $link, Meta $meta, Picture $image, string $summary = null, string $authors = null)
    {
        Assertion::allIsInstanceOf($subjects, Link::class);

        if (!empty($subjects)) {
            $this->subjects = ['list' => $subjects];
        }

        $this->link = $link;
        $this->summary = $summary;
        $this->authors = $authors;
        $this->meta = $meta;
        $this->image = $image;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/highlight-item.mustache';
    }
}
