<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block\MathML;
use eLife\Journal\ViewModel\Converter\Block\MathConverter;
use eLife\Patterns\ViewModel;

final class MathConverterTest extends BlockConverterTestCase
{
    protected $class = MathML::class;
    protected $viewModelClass = ViewModel\Math::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new MathConverter();
    }

    public function blocks() : array
    {
        return [
            'minimum' => [
                [
                    'mathml' => '<math><mrow><mi>a</mi><mo>+</mo><mi>b</mi></mrow></math>',
                ],
            ],
            'complete' => [
                [
                    'mathml' => '<math><mrow><mi>a</mi><mo>+</mo><mi>b</mi></mrow></math>',
                    'id' => 'id',
                    'label' => 'label',
                ],
            ],
        ];
    }
}
