<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasBanner;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel\Picture;

trait CreatesCoverPicture
{
    final private function highlightItemCoverPicture(HasBanner $object): Picture
    {
        return $this->coverPicture($object, 339, 190);
    }

    final private function heroBannerCoverPicture(HasBanner $object): Picture
    {
        return $this->coverPicture($object, 633, 367);
    }
    
    final private function coverPicture(HasBanner $object, int $defaultWidth, int $defaultHeight = null): Picture
    {
        return (new PictureBuilderFactory())->forImage(
            $object->getBanner(), $defaultWidth, $defaultHeight
        )->build();
    }
}
