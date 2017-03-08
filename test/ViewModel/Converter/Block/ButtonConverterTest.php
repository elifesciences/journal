<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\Button;
use eLife\Journal\ViewModel\Converter\Block\ButtonConverter;
use eLife\Patterns\ViewModel;

final class ButtonConverterTest extends BlockConverterTestCase
{
    protected $class = Button::class;
    protected $viewModelClass = ViewModel\Button::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ButtonConverter();
    }

    public function blocks() : array
    {
        return [
            'minimum' => [
                [
                    'text' => 'foo',
                    'uri' => 'http://www.example.com/',
                ],
            ],
        ];
    }
}
