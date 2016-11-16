<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\Journal\ViewModel\Converter\ArticleSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class ArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $class = ArticleVersion::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleSecondaryTeaserConverter($this->stubUrlGenerator());
    }
}
