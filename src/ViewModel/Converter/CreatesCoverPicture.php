<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasBanner;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel\Picture;

trait CreatesCoverPicture
{
    private function highlightItemCoverPicture(HasBanner $object): Picture
    {
        return $this->coverPicture($object, 368, 207);
    }
    
    private function coverPicture(HasBanner $object, int $defaultWidth, int $defaultHeight = null): Picture
    {
        return (new PictureBuilderFactory())->forImage(
            $object->getBanner(), $defaultWidth, $defaultHeight
        )->build();
    }
}
