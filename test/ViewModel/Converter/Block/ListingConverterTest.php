<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\Journal\ViewModel\Converter\Block\ListingConverter;
use eLife\ApiSdk\Model\Block\Listing;
use eLife\Patterns\ViewModel;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;

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

    public function blocks()
    {
        return [
            [
                [
                    'prefix' => 'bullet',
                    'items' => [
                        'First element',
                        'Second element',
                    ],
                ],
            ],
        ];
    }
}
