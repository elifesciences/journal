<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\VideoConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class VideoConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Video::class;
    protected $viewModelClasses = [ViewModel\Video::class, ViewModel\CaptionedAsset::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new VideoConverter(
            $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class)
        );
    }
}
