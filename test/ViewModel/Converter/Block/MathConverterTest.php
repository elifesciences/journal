<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\MathConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class MathConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\MathML::class;
    protected $viewModelClasses = [ViewModel\Math::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new MathConverter();
    }
}
