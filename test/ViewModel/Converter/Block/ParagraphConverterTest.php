<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\ParagraphConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class ParagraphConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Paragraph::class;
    protected $viewModelClasses = [ViewModel\Paragraph::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ParagraphConverter();
    }
}
