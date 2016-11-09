<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Image;
use eLife\Patterns\ViewModel;

trait CreatesTeaserImage
{
    final private function prominentTeaserImage(Image $image): ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::prominent(
            $image->getSize('16:9')->getImage(250),
            $image->getAltText(),
            [
                500 => $image->getSize('16:9')->getImage(500),
                250 => $image->getSize('16:9')->getImage(250),
            ]
        );
    }

    final private function bigTeaserImage(Image $image) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::big(
            $image->getSize('16:9')->getImage(250),
            $image->getAltText(),
            [
                500 => $image->getSize('16:9')->getImage(500),
                250 => $image->getSize('16:9')->getImage(250),
            ]
        );
    }

    final private function smallTeaserImage(Image $image) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::small(
            $image->getSize('1:1')->getImage(70),
            $image->getAltText(),
            [
                140 => $image->getSize('1:1')->getImage(140),
                70 => $image->getSize('1:1')->getImage(70),
            ]
        );
    }
}
