<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\MediumArticle;
use eLife\Journal\ViewModel\Converter\MediumArticleSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class MediumArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    // multiple models
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
        $this->converter = new MediumArticleSecondaryTeaserConverter();
    }

    protected function dataHook(array $model) : array
    {
        return $model['items'][0];
    }
}
