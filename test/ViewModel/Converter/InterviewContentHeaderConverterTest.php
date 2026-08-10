<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Journal\ViewModel\Converter\InterviewContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNew;
use PHPUnit\Framework\Attributes\Before;

final class InterviewContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['interview'];
    protected $viewModelClasses = [ContentHeaderNew::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new InterviewContentHeaderConverter($this->stubUrlGenerator());
    }
}
