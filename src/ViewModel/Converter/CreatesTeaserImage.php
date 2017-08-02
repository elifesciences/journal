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
            $this->iiifUri($object->getThumbnail(), 500, 281),
            $object->getThumbnail()->getAltText()
        );
    }

    final private function bigTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::big(
            $this->iiifUri($object->getThumbnail(), 250, 141),
            $this->iiifUri($object->getThumbnail(), 500, 281),
            $object->getThumbnail()->getAltText()
        );
    }

    final private function smallTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::small(
            $this->iiifUri($object->getThumbnail(), 70, 70),
            $this->iiifUri($object->getThumbnail(), 140, 140),
            $object->getThumbnail()->getAltText()
        );
    }
}
