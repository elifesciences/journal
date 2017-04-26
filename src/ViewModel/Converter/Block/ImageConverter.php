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
        $image = $object->getImage()->getImage();

        $srcset = [];
        $baseWidth = 538;
        if ($image->getWidth() > $baseWidth) {
            $width = $baseWidth * 2;
            if ($width > $image->getWidth()) {
                $width = $image->getWidth();
            }
            $srcset[$width] = $this->iiifUri($image, $width);
        }

        return new CaptionlessImage(
            new ViewModel\Image(
                $this->iiifUri($image, $image->getWidth() >= $baseWidth ? $baseWidth : null),
                $srcset,
                $object->getImage()->getImage()->getAltText()
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Image && !$object->getImage()->getTitle();
    }
}
