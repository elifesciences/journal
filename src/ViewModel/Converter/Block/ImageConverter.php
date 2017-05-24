<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\ViewModel\CaptionlessImage;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class ImageConverter implements ViewModelConverter
{
    use CreatesCaptionedAsset;
    use CreatesIiifUri;

    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Block\Image $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $image = $object->getImage();

        $srcset = [];
        $baseWidth = 538;
        if ($image->getWidth() > $baseWidth) {
            $width = $baseWidth * 2;
            if ($width > $image->getWidth()) {
                $width = $image->getWidth();
            }
            $srcset[$width] = $this->iiifUri($image, $width);
        }

        $imageViewModel = new ViewModel\Image(
            $this->iiifUri($image, $image->getWidth() >= $baseWidth ? $baseWidth : null),
            $srcset,
            $image->getAltText()
        );

        if (!$object->getTitle() && $object->getAttribution()->isEmpty() && $object->getCaption()->isEmpty()) {
            return new CaptionlessImage($imageViewModel);
        }

        return $this->createCaptionedAsset($imageViewModel, $object);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Image;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }

    protected function getPatternRenderer() : PatternRenderer
    {
        return $this->patternRenderer;
    }
}
