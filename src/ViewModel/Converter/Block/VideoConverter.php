<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class VideoConverter implements ViewModelConverter
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
     * @param Block\Video $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $video = new ViewModel\Video(
            array_map(function (Block\VideoSource $source) {
                return new ViewModel\MediaSource($source->getUri(), new ViewModel\MediaType($source->getMediaType()));
            }, $object->getSources()),
            $object->getPlaceholder() ? $this->iiifUri($object->getPlaceholder(), 632) : null,
            $object->isAutoplay(),
            $object->isLoop()
        );

        if (!$object->getTitle() && $object->getAttribution()->isEmpty() && $object->getCaption()->isEmpty()) {
            return $video;
        }

        return $this->createCaptionedAsset($video, $object);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Video;
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
