<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\Helper\Callback;
use eLife\Journal\ViewModel\Converter\GroupAuthorDetailsConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\AuthorDetails;
use Traversable;

final class GroupAuthorDetailsConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [AuthorDetails::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new GroupAuthorDetailsConverter($this->createMock(ViewModelConverter::class), $this->createMock(PatternRenderer::class));
    }

    /**
     * @param ArticleVersion $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getAuthors()->filter(Callback::isInstanceOf(GroupAuthor::class));
    }
}
