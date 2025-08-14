<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ListingAnnotationTeasers implements ViewModel
{
    use ListingConstructors;
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $items;
    private $id;
    private $heading;
    private $pagination;

    private function __construct(array $items, string $id = null, ListHeading $heading = null, Pager $pagination = null)
    {
        Assertion::notEmpty($items);
        Assertion::allIsInstanceOf($items, AnnotationTeaser::class);

        $this->items = $items;
        $this->id = $id;
        $this->heading = $heading;
        $this->pagination = $pagination;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/listing-annotation-teasers.mustache';
    }
}
