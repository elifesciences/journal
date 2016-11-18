<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\BoxConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class BoxConverterTest extends BlockConverterTestCase
{
    protected $class = Block\Box::class;
    protected $viewModelClass = ViewModel\Box::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new BoxConverter(
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
                    'type' => 'box',
                    'title' => 'Box title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Box content.',
                        ],
                    ],
                ],
            ],
            'complete' => [
                [
                    'type' => 'box',
                    'doi' => '10.7554/eLife.09560.005',
                    'id' => 'boxId',
                    'label' => 'Box label',
                    'title' => 'Box title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Box content.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
