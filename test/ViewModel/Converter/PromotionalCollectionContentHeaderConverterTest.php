<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PromotionalCollectionContentHeaderConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\Asset\Packages;

final class PromotionalCollectionContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['promotional-collection'];
    protected $viewModelClasses = [ViewModel\ContentHeader::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new PromotionalCollectionContentHeaderConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator(),
            new ContentHeaderImageFactory(new PictureBuilderFactory(), new Packages())
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            ));
    }
}
