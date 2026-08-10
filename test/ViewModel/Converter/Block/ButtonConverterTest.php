<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\ButtonConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class ButtonConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Button::class;
    protected $viewModelClasses = [ViewModel\ButtonCollection::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ButtonConverter();
    }
}
