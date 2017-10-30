<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\PersonProfileSnippetConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use Traversable;

final class PersonProfileSnippetConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $viewModelClasses = [ViewModel\ProfileSnippet::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PersonProfileSnippetConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class)
        );

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            )));
    }

    /**
     * @param Collection $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getCurators();
    }
}
