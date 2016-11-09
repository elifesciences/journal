<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Journal\ViewModel\Converter\BlogArticleSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlogArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['blog-article'];
    protected $class = BlogArticle::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->converter = new BlogArticleSecondaryTeaserConverter($this->urlGenerator);
    }
}
