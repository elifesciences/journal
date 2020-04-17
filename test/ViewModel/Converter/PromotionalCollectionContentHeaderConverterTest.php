<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PromotionalCollectionContentHeaderConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Asset\Packages;

final class PromotionalCollectionContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['promotional-collection'];
    protected $viewModelClasses = [ViewModel\ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PromotionalCollectionContentHeaderConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator(),
            new ContentHeaderImageFactory(new PictureBuilderFactory(), new Packages())
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
