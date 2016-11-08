<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class VideoConverter implements ViewModelConverter
{
    /**
     * @param Block\Video $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $video = new ViewModel\Video(
            $object->getImage(),
            array_map(function (Block\VideoSource $source) {
                return new ViewModel\MediaSource($source->getUri(), new ViewModel\MediaType($source->getMediaType()));
            }, $object->getSources())
        );

        return $video;
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Video;
    }
}
