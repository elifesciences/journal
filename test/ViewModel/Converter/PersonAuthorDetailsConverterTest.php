<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\Journal\Helper\Callback;
use eLife\Journal\ViewModel\Converter\PersonAuthorDetailsConverter;
use eLife\Patterns\ViewModel\AuthorDetails;
use Traversable;

final class PersonAuthorDetailsConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [AuthorDetails::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PersonAuthorDetailsConverter();
    }

    /**
     * @param ArticleVersion $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        yield from $model->getAuthors()->filter(Callback::isInstanceOf(PersonAuthor::class));
    }
}
