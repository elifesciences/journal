<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\ArticleAuthorsConverter;
use eLife\Patterns\ViewModel\Authors;
use Traversable;

final class ArticleAuthorsConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [Authors::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleAuthorsConverter(
            $this->stubUrlGenerator()
        );
    }

    /**
     * @param ArticleVersion $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getAuthors()->notEmpty()) {
            yield $model;
        }
    }
}
