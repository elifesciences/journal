<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\PersonProfileSnippetConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;
use PHPUnit\Framework\Attributes\Before;
use Traversable;

final class PersonProfileSnippetConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $viewModelClasses = [ViewModel\ProfileSnippet::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new PersonProfileSnippetConverter(
            $viewModelConverter = $this->createMock(ViewModelConverter::class)
        );

        $viewModelConverter
            ->method('convert')
            ->willReturn(new ViewModel\Picture(
                [],
                new ViewModel\Image('/image.jpg')
            ));
    }

    /**
     * @param Collection $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getCurators();
    }
}
