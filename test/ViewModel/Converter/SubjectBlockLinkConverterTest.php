<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Journal\ViewModel\Converter\SubjectBlockLinkConverter;
use eLife\Patterns\ViewModel;

final class SubjectBlockLinkConverterTest extends ModelConverterTestCase
{
    protected $models = ['subject'];
    protected $viewModelClasses = [ViewModel\BlockLink::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SubjectBlockLinkConverter($this->stubUrlGenerator());
    }
}
