<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Journal\ViewModel\Converter\InterviewContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;

final class InterviewContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['interview'];
    protected $viewModelClasses = [ContentHeaderNonArticle::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new InterviewContentHeaderConverter();
    }
}
