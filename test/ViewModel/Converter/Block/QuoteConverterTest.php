<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Quote;
use eLife\Journal\ViewModel\Converter\Block\QuoteConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\PullQuote;

final class QuoteConverterTest extends BlockConverterTestCase
{
    protected $class = Quote::class;
    protected $viewModelClass = PullQuote::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new QuoteConverter(
            $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );
        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));
    }

    public function blocks()
    {
        return [
            [
                [
                    'text' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Lorem ipsum...',
                        ],
                    ],
                ],
            ],
        ];
    }
}
