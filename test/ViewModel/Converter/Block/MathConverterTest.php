<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\MathConverter;
use eLife\Patterns\ViewModel;

final class MathConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\MathML::class;
    protected $viewModelClasses = [ViewModel\Math::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new MathConverter();
    }
}
