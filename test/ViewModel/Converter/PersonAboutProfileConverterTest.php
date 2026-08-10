<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PersonAboutProfileConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;

final class PersonAboutProfileConverterTest extends ModelConverterTestCase
{
    protected $models = ['person'];
    protected $viewModelClasses = [ViewModel\AboutProfile::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new PersonAboutProfileConverter(
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
