<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\YouTubeConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class YouTubeConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\YouTube::class;
    protected $viewModelClasses = [ViewModel\IFrame::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new YouTubeConverter(
            $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class)
        );
    }

    protected function unsupportedModelData() : array
    {
        return [
            'type' => 'paragraph',
            'text' => 'foo',
        ];
    }
}
