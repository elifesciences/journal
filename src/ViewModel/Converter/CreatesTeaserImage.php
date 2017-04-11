<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasThumbnail;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Patterns\ViewModel;

trait CreatesTeaserImage
{
    use CreatesIiifUri;

    final private function prominentTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::prominent(
            $this->iiifUri($object->getThumbnail(), 250, 141),
            $object->getThumbnail()->getAltText(),
            [
                500 => $this->iiifUri($object->getThumbnail(), 500, 281),
                250 => $this->iiifUri($object->getThumbnail(), 250, 141),
            ]
        );
    }

    final private function bigTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::big(
            $this->iiifUri($object->getThumbnail(), 250, 141),
            $object->getThumbnail()->getAltText(),
            [
                500 => $this->iiifUri($object->getThumbnail(), 500, 281),
                250 => $this->iiifUri($object->getThumbnail(), 250, 141),
            ]
        );
    }

    final private function smallTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::small(
            $this->iiifUri($object->getThumbnail(), 70, 70),
            $object->getThumbnail()->getAltText(),
            [
                140 => $this->iiifUri($object->getThumbnail(), 140, 140),
                70 => $this->iiifUri($object->getThumbnail(), 70, 70),
            ]
        );
    }
}
