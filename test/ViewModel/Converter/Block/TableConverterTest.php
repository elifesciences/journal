<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Table;
use eLife\Journal\ViewModel\Converter\Block\TableConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

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
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
    }

    public function blocks() : array
    {
        return [
            'minimum' => [
                [
                    'tables' => [
                        '<table>...</table>',
                    ],
                ],
            ],
            'complete' => [
                [
                    'tables' => [
                        '<table>...</table>',
                    ],
                    'footnotes' => [
                        [
                            'id' => 'id',
                            'label' => 'label',
                            'text' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'footnote 1',
                                ],
                            ],
                        ],
                        [
                            'text' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'footnote 2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
