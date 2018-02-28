<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SubjectTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class SubjectTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['subject'];
    protected $viewModelClasses = [ViewModel\Teaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SubjectTeaserConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator(),
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
