<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\ViewModel\CaptionlessImage;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class ImageConverter implements ViewModelConverter
{
    use CreatesIiifUri;

    /**
     * @param Block\Image $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new CaptionlessImage(
            new ViewModel\Image(
                $this->iiifUri($object->getImage()->getImage(), 538),
                [1076 => $this->iiifUri($object->getImage()->getImage(), 1076)],
                $object->getImage()->getImage()->getAltText()
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Image && !$object->getImage()->getTitle();
    }
}
