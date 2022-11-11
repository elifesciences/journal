<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasBanner;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel\Picture;

trait CreatesCoverPicture
{
    private function createHighlightPicture(HasBanner $object): Picture
    {
        return (new PictureBuilderFactory())->forImage(
            $object->getBanner(), 339, 190
        )->build();
    }

    private function createHeroBannerPicture(HasBanner $object): Picture
    {
        return (new PictureBuilderFactory())->forImage(
            $object->getBanner(), 633, 367
        )->build();
    }
}
