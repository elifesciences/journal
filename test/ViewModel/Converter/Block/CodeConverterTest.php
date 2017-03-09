<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Code;
use eLife\Journal\ViewModel\Converter\Block\CodeConverter;
use eLife\Patterns\ViewModel;

final class CodeConverterTest extends BlockConverterTestCase
{
    protected $class = Code::class;
    protected $viewModelClass = ViewModel\Code::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CodeConverter();
    }

    public function blocks() : array
    {
        return [
            'minimum' => [
                [
                    'code' => '<p>foo</p>',
                ],
            ],
            'complete' => [
                [
                    'code' => "<p>foo</p>\n<p>bar</p>",
                    'language' => 'html',
                ],
            ],
        ];
    }
}
