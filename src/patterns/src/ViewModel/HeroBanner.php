<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class HeroBanner implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;
    use HasTitleLength;

    private $title;
    private $titleLength;
    private $summary;
    private $authors;
    private $subjects;
    private $image;
    private $url;
    private $meta;

    public function __construct(
        array $subjects, Link $title, Meta $meta, Picture $image, string $summary = null, string $authorLine = null
    )
    {
        Assertion::allIsInstanceOf($subjects, Link::class);

        if (!empty($subjects)) {
            $this->subjects = ['list' => $subjects];
        }
        $this->summary = $summary;
        $this->title = $title['name'];
        $this->titleLength = $this->determineTitleLength($this->title, [
            49 => 'short',
            89 => 'medium',
            null => 'long',
        ]);
        $this->url = $title['url'];
        $this->meta = $meta;
        $this->image = $image;
        $this->authors = $authorLine;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/hero-banner.mustache';
    }
}
