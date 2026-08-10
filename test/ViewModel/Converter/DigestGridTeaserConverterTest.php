<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\DigestGridTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class DigestGridTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['digest'];
    protected $viewModelClasses = [ViewModel\Teaser::class];
    protected $context = ['variant' => 'grid'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new DigestGridTeaserConverter(
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
