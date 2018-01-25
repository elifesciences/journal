<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\AnnotationAnnotationTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\AnnotationTeaser;
use eLife\Patterns\ViewModel\Paragraph;

final class AnnotationAnnotationTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['annotation'];
    protected $viewModelClasses = [AnnotationTeaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $viewModelConverter = $this->createMock(ViewModelConverter::class);
        $viewModelConverter->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(new Paragraph('foo')));

        $patternRenderer = $this->createMock(PatternRenderer::class);
        $patternRenderer
            ->expects($this->any())
            ->method('render')
            ->will($this->returnValue('...'));

        $this->converter = new AnnotationAnnotationTeaserConverter($viewModelConverter, $patternRenderer);
    }
}
