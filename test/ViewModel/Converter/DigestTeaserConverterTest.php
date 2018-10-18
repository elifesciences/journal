<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\DigestTeaserConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class DigestTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['digest'];
    protected $viewModelClasses = [ViewModel\Teaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new DigestTeaserConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
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
