<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\AnnotationAnnotationTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\AnnotationTeaser;
use eLife\Patterns\ViewModel\Paragraph;
use PHPUnit\Framework\Attributes\Before;

final class AnnotationAnnotationTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['annotation'];
    protected $viewModelClasses = [AnnotationTeaser::class];

    #[Before]
    public function setUpConverter(): void
    {
        $viewModelConverter = $this->createMock(ViewModelConverter::class);
        $viewModelConverter
            ->method('convert')
            ->willReturn(new Paragraph('foo'));

        $patternRenderer = $this->createMock(PatternRenderer::class);
        $patternRenderer
            ->method('render')
            ->willReturn('...');

        $this->converter = new AnnotationAnnotationTeaserConverter($viewModelConverter, $patternRenderer);
    }
}
