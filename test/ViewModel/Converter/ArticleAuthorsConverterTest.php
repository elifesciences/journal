<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\ArticleAuthorsConverter;
use eLife\Patterns\ViewModel\Authors;
use PHPUnit\Framework\Attributes\Before;
use Traversable;

final class ArticleAuthorsConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [Authors::class];

    #[Before]
    public function setUpConverter(): void
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
