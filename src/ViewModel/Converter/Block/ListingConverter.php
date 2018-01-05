<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class ListingConverter implements ViewModelConverter
{
    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Block\Listing $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $context['level'] = ($context['level'] ?? 1) + 1;

        $items = $object->getItems()->map(function ($listingItems) use ($context) {
            if ($listingItems instanceof Sequence) {
                return implode('', $listingItems->map(function (Block $block) use ($context) {
                    return $this->patternRenderer->render($this->viewModelConverter->convert($block, null, $context));
                })->toArray());
            }

            return $listingItems;
        })->toArray();

        switch ($object->getPrefix()) {
            case 'alpha-lower':
            case 'alpha-upper':
            case 'number':
            case 'roman-lower':
            case 'roman-upper':
                return ViewModel\Listing::ordered($items, $object->getPrefix());
            case 'bullet':
                return ViewModel\Listing::unordered($items, $object->getPrefix());
        }

        return ViewModel\Listing::unordered($items);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Listing;
    }
}
