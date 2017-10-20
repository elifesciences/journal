<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\ViewModel\Converter\CollectionContentHeaderConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Asset\Packages;

final class CollectionContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $viewModelClasses = [ViewModel\ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CollectionContentHeaderConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator(),
            new ContentHeaderImageFactory(new Packages())
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
