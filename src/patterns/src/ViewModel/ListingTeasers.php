<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;
use InvalidArgumentException;

final class ListingTeasers implements ViewModel
{
    use ListingConstructors;
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $items;
    private $id;
    private $heading;
    private $pagination;
    private $seeMoreLink;
    private $highlights;

    private function __construct(array $items, string $id = null, ListHeading $heading = null, Pager $pagination = null, SeeMoreLink $seeMoreLink = null, bool $highlights = false)
    {
        if (
            null !== $pagination &&
            null !== $seeMoreLink
        ) {
            throw new InvalidArgumentException('You cannot have both Pager and SeeMoreLink in Teaser Listings.');
        }
        Assertion::notEmpty($items);
        Assertion::allIsInstanceOf($items, Teaser::class);
        $this->items = $items;
        $this->id = $id;
        $this->heading = $heading;
        $this->pagination = $pagination;
        $this->seeMoreLink = $seeMoreLink;
        $this->highlights = $highlights;
        if ($highlights) {
            Assertion::min(count($items), 3, 'Highlights must be at least 3 items', 'items');
            $this->items = array_map(function (Teaser $teaser, int $num) {
                return FlexibleViewModel::fromViewModel($teaser)
                    ->withProperty('rootClasses', $teaser['rootClasses'].' teaser--highlights')
                    ->withProperty('carouselItem', true)
                    ->withProperty('num', $num);
            }, $this->items, range(1, count($this->items)));
        }
    }

    public static function forHighlights(array $items, ListHeading $heading, string $id) : ViewModel
    {
        $viewModel = new static($items, $id, $heading, null, null, true);

        return $viewModel;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/listing-teasers.mustache';
    }
}
