<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class GoogleMapConverter implements ViewModelConverter
{
    /**
     * @param Block\GoogleMap $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\IFrame('https://www.google.com/maps/d/embed?mid='.$object->getId(), 16, 9, $object->getTitle());
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\GoogleMap;
    }
}
