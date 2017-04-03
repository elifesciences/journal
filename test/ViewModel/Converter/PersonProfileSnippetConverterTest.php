<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\PersonProfileSnippetConverter;
use eLife\Patterns\ViewModel\ProfileSnippet;
use Traversable;

final class PersonProfileSnippetConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $viewModelClasses = [ProfileSnippet::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PersonProfileSnippetConverter();
    }

    /**
     * @param Collection $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getCurators();
    }
}
