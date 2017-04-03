<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\ReviewerProfileSnippetConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel\ProfileSnippet;
use Traversable;

final class ReviewerProfileSnippetConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ProfileSnippet::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ReviewerProfileSnippetConverter($this->createMock(ViewModelConverter::class));
    }

    /**
     * @param ArticleVersion $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getReviewers();
    }
}
