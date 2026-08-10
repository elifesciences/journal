<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\FigshareConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class FigshareConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Figshare::class;
    protected $viewModelClasses = [ViewModel\IFrame::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new FigshareConverter();
    }

    protected function unsupportedModelData() : array
    {
        return [
            'type' => 'paragraph',
            'text' => 'foo',
        ];
    }
}
