<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Journal\ViewModel\Converter\InterviewSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class InterviewSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['interview'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new InterviewSecondaryTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }
}
