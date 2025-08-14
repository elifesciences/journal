<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class GridListing implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $classes;
    private $heading;
    private $blockLinks;
    private $imageLinks;
    private $archiveNavLinks;
    private $teasers;
    private $pagination;
    private $id;

    private function __construct(
        string $classes = null,
        ListHeading $heading = null,
        array $blockLinks = [],
        array $archiveNavLinks = [],
        array $imageLinks = [],
        array $teasers = [],
        Pager $pagination = null,
        string $id = null
    ) {
        $this->classes = $classes;
        $this->heading = $heading;
        $this->blockLinks = $blockLinks;
        $this->imageLinks = $imageLinks;
        $this->archiveNavLinks = $archiveNavLinks;
        $this->teasers = $teasers;
        $this->pagination = $pagination;
        $this->id = $id;
    }

    public static function forBlockLinks(array $blockLinks, ListHeading $heading = null) : GridListing
    {
        Assertion::notEmpty($blockLinks);
        Assertion::allIsInstanceOf($blockLinks, BlockLink::class);

        $blockLinks = array_map(function (BlockLink $blockLink) {
            $blockLink = FlexibleViewModel::fromViewModel($blockLink);

            return $blockLink->withProperty('isGridListing', true);
        }, $blockLinks);

        return new self('grid-listing--block-link', $heading, $blockLinks);
    }

    public static function forArchiveNavLinks(array $archiveNavLinks, ListHeading $heading = null) : GridListing
    {
        Assertion::notEmpty($archiveNavLinks);
        Assertion::allIsInstanceOf($archiveNavLinks, ArchiveNavLink::class);

        return new self(null, $heading, [], $archiveNavLinks);
    }

    public static function forImageLinks(array $imageLinks, ListHeading $heading = null) : GridListing
    {
        Assertion::notEmpty($imageLinks);
        Assertion::allIsInstanceOf($imageLinks, ImageLink::class);

        return new self('grid-listing--image-link', $heading, [], [], $imageLinks);
    }

    public static function forTeasers(array $teasers, ListHeading $heading = null, Pager $pagination = null, string $id = null) : GridListing
    {
        Assertion::notEmpty($teasers);
        Assertion::allIsInstanceOf($teasers, Teaser::class);

        return new self(null, $heading, [], [], [], $teasers, $pagination, $id);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/grid-listing.mustache';
    }
}
