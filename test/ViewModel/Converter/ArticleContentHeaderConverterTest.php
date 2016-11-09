<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticlePoA;
use eLife\Journal\ViewModel\Converter\ArticleContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderArticle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleContentHeaderConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $model = 'article-poa';
    protected $class = ArticlePoA::class;
    protected $viewModelClass = ContentHeaderArticle::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->converter = new ArticleContentHeaderConverter($this->urlGenerator);
    }
}
