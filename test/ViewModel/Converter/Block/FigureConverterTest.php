<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\AssetViewerInlineSet;
use eLife\Journal\ViewModel\Converter\Block\FigureConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class FigureConverterTest extends BlockConverterTestCase
{
    protected string $blockClass = Block\Figure::class;
    protected $viewModelClasses = [AssetViewerInlineSet::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new FigureConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class)
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(ViewModel\AssetViewerInline::primary(
                'some-id',
                'Some label',
                new ViewModel\CaptionedAsset(
                    new ViewModel\Image('/image.jpg')
                )
            ));
    }
}
