<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\LabsPostTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class LabsPostTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-post'];
    protected $viewModelClasses = [ViewModel\Teaser::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new LabsPostTeaserConverter(
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
