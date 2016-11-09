<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\Journal\ViewModel\Converter\ArticleSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['article-poa', /*'article-vor'*/];
    protected $class = ArticleVersion::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->converter = new ArticleSecondaryTeaserConverter($this->urlGenerator);
    }
}
