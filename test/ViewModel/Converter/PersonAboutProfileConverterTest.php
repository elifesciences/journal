<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PersonAboutProfileConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class PersonAboutProfileConverterTest extends ModelConverterTestCase
{
    protected $models = ['person'];
    protected $viewModelClasses = [ViewModel\AboutProfile::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PersonAboutProfileConverter(
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
