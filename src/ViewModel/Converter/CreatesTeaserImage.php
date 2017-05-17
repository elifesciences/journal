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
            $this->iiifUri($object->getThumbnail(), 263, 148),
            $object->getThumbnail()->getAltText(),
            [
                526 => $this->iiifUri($object->getThumbnail(), 526, 296),
                263 => $this->iiifUri($object->getThumbnail(), 263, 148),
            ]
        );
    }

    final private function bigTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::big(
            $this->iiifUri($object->getThumbnail(), 257, 144),
            $object->getThumbnail()->getAltText(),
            [
                514 => $this->iiifUri($object->getThumbnail(), 514, 288),
                257 => $this->iiifUri($object->getThumbnail(), 257, 144),
            ]
        );
    }

    final private function smallTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::small(
            $this->iiifUri($object->getThumbnail(), 72, 72),
            $object->getThumbnail()->getAltText(),
            [
                144 => $this->iiifUri($object->getThumbnail(), 144, 144),
                72 => $this->iiifUri($object->getThumbnail(), 72, 72),
            ]
        );
    }
}
