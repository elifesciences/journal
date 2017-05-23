<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\TableConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class TableConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Table::class;
    protected $viewModelClasses = [ViewModel\Table::class, ViewModel\CaptionedAsset::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new TableConverter(
            $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class)
        );
    }
}
