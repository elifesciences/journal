<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\Helper\Callback;
use eLife\Patterns\ViewModel\Reference;
use test\eLife\Journal\ViewModel\Converter\ModelConverterTestCase;
use Traversable;

abstract class ReferenceConverterTestCase extends ModelConverterTestCase
{
    protected $models = ['article-vor'];
    protected $viewModelClasses = [Reference::class];

    /**
     * @param ArticleVoR $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getReferences()->filter(Callback::isInstanceOf($this->referenceClass));
    }
}
