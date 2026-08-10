<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\ProfileConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class ProfileConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Profile::class;
    protected $viewModelClasses = [ViewModel\InlineProfile::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ProfileConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $patternRenderer = $this->createMock(PatternRenderer::class)
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            ));
        $patternRenderer
            ->method('render')
            ->willReturn('...');
    }
}
