<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Table;
use eLife\Journal\ViewModel\Converter\Block\TableConverter;
use eLife\Journal\ViewModel;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;

final class TableConverterTest extends BlockConverterTestCase
{
    protected $class = Table::class;
    protected $viewModelClass = ViewModel\Table::class;

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

    public function blocks()
    {
        return [
            [
                [
                    'tables' => [
                    ],
                ],
            ],
        ];
    }
}
