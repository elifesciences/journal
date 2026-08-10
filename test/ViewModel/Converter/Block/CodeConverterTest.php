<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\CodeConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class CodeConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Code::class;
    protected $viewModelClasses = [ViewModel\Code::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new CodeConverter();
    }
}
