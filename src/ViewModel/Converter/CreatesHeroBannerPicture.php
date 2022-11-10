<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasBanner;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel\Picture;

trait CreatesHeroBannerPicture
{
    private function createHeroBannerPicture(HasBanner $banner): Picture
    {
        return (new PictureBuilderFactory())->forImage(
            $banner->getBanner(), 633, 367
        )->build();
    }
}