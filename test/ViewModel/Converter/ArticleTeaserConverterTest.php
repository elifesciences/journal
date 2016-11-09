<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\Journal\ViewModel\Converter\ArticleTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleTeaserConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['article-poa', /*'article-vor'*/];
    protected $class = ArticleVersion::class;
    protected $viewModelClass = Teaser::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->converter = new ArticleTeaserConverter($this->urlGenerator);
    }
}
