<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Listing;
use eLife\Journal\ViewModel\Converter\Block\ListingConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class ListingConverterTest extends BlockConverterTestCase
{
    protected $class = Listing::class;
    protected $viewModelClass = ViewModel\Listing::class;

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

    public function blocks() : array
    {
        return [
            'unordered' => [
                [
                    'prefix' => 'bullet',
                    'items' => [
                        'First element',
                        'Second element',
                    ],
                ],
            ],
            'ordered' => [
                [
                    'prefix' => 'number',
                    'items' => [
                        'First element',
                        'Second element',
                    ],
                ],
            ],
            'with paragraph' => [
                [
                    'prefix' => 'bullet',
                    'items' => [
                        'First element',
                        [
                            [
                                'type' => 'paragraph',
                                'text' => 'Second element',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
