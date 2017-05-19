<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\AssetViewerInlineSet;
use eLife\Journal\ViewModel\Converter\Block\FigureConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class FigureConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Figure::class;
    protected $viewModelClasses = [AssetViewerInlineSet::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new FigureConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class)
        );

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(ViewModel\AssetViewerInline::primary(
                'some-id',
                'Some label',
                new ViewModel\CaptionedAsset(
                    new ViewModel\Image('/image.jpg'),
                    new ViewModel\CaptionText('Some caption')
                )
            )));
    }
}
