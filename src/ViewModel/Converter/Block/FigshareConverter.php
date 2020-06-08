<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class FigshareConverter implements ViewModelConverter
{
    /**
     * @param Block\Figshare $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\IFrame('https://widgets.figshare.com/articles/'.$object->getId().'/embed', $object->getWidth(), $object->getHeight(), $object->getTitle());
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Figshare;
    }
}
