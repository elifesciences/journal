<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\ImageConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class ImageConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Image::class;
    protected $viewModelClasses = [ViewModel\CaptionedAsset::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ImageConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class)
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            ));
    }
}
