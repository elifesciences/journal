<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\GoogleMapConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class GoogleMapConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\GoogleMap::class;
    protected $viewModelClasses = [ViewModel\IFrame::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new GoogleMapConverter();
    }

    protected function unsupportedModelData() : array
    {
        return [
            'type' => 'paragraph',
            'text' => 'foo',
        ];
    }
}
