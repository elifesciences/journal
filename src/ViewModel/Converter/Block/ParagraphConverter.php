<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\ViewModel;

final class ParagraphConverter implements ViewModelConverter
{
    /**
     * @param Block\Paragraph $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new Paragraph($object->getText());
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Paragraph;
    }
}
