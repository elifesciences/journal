<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class SectionListing implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $id;
    private $sections;
    private $listHeading;
    private $singleLine;

    public function __construct(string $id, array $sections, ListHeading $listHeading, bool $singleLine = false)
    {
        Assertion::notBlank($id);
        Assertion::allIsInstanceOf($sections, Link::class);
        Assertion::notEmpty($sections);

        $this->id = $id;
        $this->sections = $sections;
        $this->singleLine = $singleLine;
        $this->listHeading = $listHeading;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/section-listing.mustache';
    }
}
