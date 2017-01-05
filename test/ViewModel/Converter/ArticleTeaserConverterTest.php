<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\Journal\ViewModel\Converter\ArticleTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class ArticleTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $class = ArticleVersion::class;
    protected $viewModelClass = Teaser::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleTeaserConverter($this->stubUrlGenerator(), $this->stubTranslator());
    }
}
