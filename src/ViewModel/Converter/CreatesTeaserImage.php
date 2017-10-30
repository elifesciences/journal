<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasThumbnail;
use eLife\Journal\Helper\HasViewModelConverter;
use eLife\Patterns\ViewModel;

trait CreatesTeaserImage
{
    use HasViewModelConverter;

    final private function prominentTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::prominent(
            $this->getViewModelConverter()->convert($object->getThumbnail(), null, ['width' => 250, 'height' => 141])
        );
    }

    final private function bigTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::big(
            $this->getViewModelConverter()->convert($object->getThumbnail(), null, ['width' => 250, 'height' => 141])
        );
    }

    final private function smallTeaserImage(HasThumbnail $object) : ViewModel\TeaserImage
    {
        return ViewModel\TeaserImage::small(
            $this->getViewModelConverter()->convert($object->getThumbnail(), null, ['width' => 70, 'height' => 70])
        );
    }
}
