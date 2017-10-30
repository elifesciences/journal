<?php

namespace test\eLife\Journal\ViewModel\Converter\Block;

use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\Block\ImageConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class ImageConverterTest extends BlockConverterTestCase
{
    protected $blockClass = Block\Image::class;
    protected $viewModelClasses = [ViewModel\CaptionedAsset::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ImageConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class)
        );

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            )));
    }
}
