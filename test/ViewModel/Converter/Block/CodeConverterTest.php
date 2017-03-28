<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\CodeConverter;
use eLife\Patterns\ViewModel;

final class CodeConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Code::class;
    protected $viewModelClasses = [ViewModel\Code::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CodeConverter();
    }
}
