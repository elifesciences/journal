<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class YouTubeConverter implements ViewModelConverter
{
    /**
     * @param Block\YouTube $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\IFrame('https://www.youtube.com/embed/'.$object->getId(), $object->getWidth(), $object->getHeight());
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\YouTube;
    }
}
