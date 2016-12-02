<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasThumbnail;
use eLife\Patterns\ViewModel;

trait CreatesTeaserImage
{
    final private function prominentTeaserImage(HasThumbnail $object): ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::prominent(
            $object->getThumbnail()->getSize('16:9')->getImage(250),
            $object->getThumbnail()->getAltText(),
            [
                500 => $object->getThumbnail()->getSize('16:9')->getImage(500),
                250 => $object->getThumbnail()->getSize('16:9')->getImage(250),
            ]
        );
    }

    final private function bigTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::big(
            $object->getThumbnail()->getSize('16:9')->getImage(250),
            $object->getThumbnail()->getAltText(),
            [
                500 => $object->getThumbnail()->getSize('16:9')->getImage(500),
                250 => $object->getThumbnail()->getSize('16:9')->getImage(250),
            ]
        );
    }

    final private function smallTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::small(
            $object->getThumbnail()->getSize('1:1')->getImage(70),
            $object->getThumbnail()->getAltText(),
            [
                140 => $object->getThumbnail()->getSize('1:1')->getImage(140),
                70 => $object->getThumbnail()->getSize('1:1')->getImage(70),
            ]
        );
    }
}
