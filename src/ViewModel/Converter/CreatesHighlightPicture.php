<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasBanner;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel\Picture;

trait CreatesHighlightPicture
{
    private function createHighlightPicture(HasBanner $banner): Picture
    {
        return (new PictureBuilderFactory())->forImage(
            $banner->getBanner(), 339, 190
        )->build();
    }
}