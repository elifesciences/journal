<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\ButtonConverter;
use eLife\Patterns\ViewModel;

final class ButtonConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Button::class;
    protected $viewModelClasses = [ViewModel\ButtonCollection::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ButtonConverter();
    }
}
