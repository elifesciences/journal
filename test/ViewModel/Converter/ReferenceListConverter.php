<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasReferences;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\ReferenceListConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Reference;
use eLife\Patterns\ViewModel\ReferenceList;
use Traversable;

final class ReferenceListConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-vor'];
    protected $viewModelClasses = [ReferenceList::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ReferenceListConverter($viewModelConverter = $this->createMock(ViewModelConverter::class));

        $viewModelConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(Reference::withOutDoi(new Link('title'))));
    }

    /**
     * @param HasReferences $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getReferences()->notEmpty()) {
            yield $model;
        }
    }
}
