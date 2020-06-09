<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\FigshareConverter;
use eLife\Patterns\ViewModel;

final class FigshareConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Figshare::class;
    protected $viewModelClasses = [ViewModel\IFrame::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new FigshareConverter();
    }

    protected function unsupportedModelData() : array
    {
        return [
            'type' => 'paragraph',
            'text' => 'foo',
        ];
    }
}
