<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\LabsPostGridTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class LabsPostGridTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-post'];
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'grid'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new LabsPostGridTeaserConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            ));
    }
}
