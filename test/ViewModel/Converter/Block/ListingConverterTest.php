<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\ListingConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class ListingConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Listing::class;
    protected $viewModelClasses = [ViewModel\Listing::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ListingConverter(
            $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class)
        );
    }
}
