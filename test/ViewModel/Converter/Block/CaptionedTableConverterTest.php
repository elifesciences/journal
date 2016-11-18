<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Table;
use eLife\Journal\ViewModel\Converter\Block\CaptionedTableConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\CaptionedAsset;

final class CaptionedTableConverterTest extends BlockConverterTestCase
{
    protected $class = Table::class;
    protected $viewModelClass = CaptionedAsset::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CaptionedTableConverter(
            $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class)
        );
    }

    public function blocks() : array
    {
        return [
            'minimum' => [
                [
                    'title' => 'Table\'s caption',
                    'tables' => [
                    ],
                ],
            ],
            'with paragraph caption' => [
                [
                    'title' => 'Table\'s caption',
                    'tables' => [
                    ],
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'A table\'s caption',
                        ],
                    ],
                ],

            ],
            'with MathML caption' => [
                [
                    'title' => 'Table\'s caption',
                    'tables' => [
                    ],
                    'caption' => [
                        [
                            'type' => 'mathml',
                            'mathml' => '<math>A table\'s caption</math>',
                        ],
                    ],
                ],
            ],
        ];
    }
}
