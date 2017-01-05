<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\Journal\ViewModel\Converter\ArticleMetaConverter;
use eLife\Patterns\ViewModel\ArticleMeta;

final class ArticleMetaConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $class = ArticleVersion::class;
    protected $viewModelClass = ArticleMeta::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleMetaConverter();
    }
}
