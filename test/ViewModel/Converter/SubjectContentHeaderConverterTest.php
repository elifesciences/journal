<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Journal\ViewModel\Converter\SubjectContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;

final class SubjectContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $model = 'subject';
    protected $class = Subject::class;
    protected $viewModelClass = ContentHeaderNonArticle::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SubjectContentHeaderConverter();
    }
}
