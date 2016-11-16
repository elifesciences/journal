<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Journal\ViewModel\Converter\InterviewTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class InterviewTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['interview'];
    protected $class = Interview::class;
    protected $viewModelClass = Teaser::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new InterviewTeaserConverter($this->stubUrlGenerator());
    }
}
