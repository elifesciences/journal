<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\Journal\ViewModel\Converter\Block\ParagraphConverter;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\Journal\ViewModel;

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
            [
                [
                    'text' => 'Lorem ipsum...',
                ],
            ],
        ];
    }
}
