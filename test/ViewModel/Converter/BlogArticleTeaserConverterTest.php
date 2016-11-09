<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Journal\ViewModel\Converter\BlogArticleTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlogArticleTeaserConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['blog-article'];
    protected $class = BlogArticle::class;
    protected $viewModelClass = Teaser::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->converter = new BlogArticleTeaserConverter($this->urlGenerator);
    }
}
