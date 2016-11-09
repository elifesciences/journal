<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\MediumArticle;
use eLife\Journal\ViewModel\Converter\MediumArticleSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class MediumArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['medium-article-list'];
    protected $class = MediumArticle::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'secondary'];
    protected $samples = 'first-page';

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->converter = new MediumArticleSecondaryTeaserConverter($this->urlGenerator);
    }

    protected function modelHook(array $model) : array
    {
        return $model['items'][0];
    }
}
