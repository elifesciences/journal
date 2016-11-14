<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\Journal\ViewModel;
use eLife\Journal\ViewModel\Converter\Block\ParagraphConverter;

final class ParagraphConverterTest extends BlockConverterTestCase
{
    protected $class = Paragraph::class;
    protected $viewModelClass = ViewModel\Paragraph::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ParagraphConverter();
    }

    public function blocks()
    {
        return [
            'minimum' => [
                [
                    'text' => 'Lorem ipsum...',
                ],
            ],
        ];
    }
}
