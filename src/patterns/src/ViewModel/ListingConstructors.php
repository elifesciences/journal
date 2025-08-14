<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ViewModel;

trait ListingConstructors
{
    public static function withSeeMore(array $items, SeeMoreLink $seeMoreLink, ListHeading $heading = null, string $id = null) : ViewModel
    {
        return new static($items, $id, $heading, null, $seeMoreLink);
    }

    public static function withPagination(array $items, Pager $pagination, ListHeading $heading = null, string $id = null) : ViewModel
    {
        return new static($items, $id, $heading, $pagination);
    }

    public static function basic(array $items, ListHeading $heading = null, string $id = null) : ViewModel
    {
        return new static ($items, $id, $heading);
    }
}
