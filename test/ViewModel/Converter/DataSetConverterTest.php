<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\DataSetConverter;
use eLife\Patterns\ViewModel\Reference;
use Traversable;

final class DataSetConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [Reference::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new DataSetConverter();
    }

    /**
     * @param ArticleVersion $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getGeneratedDataSets();
        yield from $model->getUsedDataSets();
    }
}
