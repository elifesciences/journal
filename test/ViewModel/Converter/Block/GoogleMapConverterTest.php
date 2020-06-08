<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\GoogleMapConverter;
use eLife\Patterns\ViewModel;

final class GoogleMapConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\GoogleMap::class;
    protected $viewModelClasses = [ViewModel\IFrame::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new GoogleMapConverter();
    }

    protected function unsupportedModelData() : array
    {
        return [
            'type' => 'paragraph',
            'text' => 'foo',
        ];
    }
}
