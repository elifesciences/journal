<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\JobAdvertTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use PHPUnit\Framework\Attributes\Before;

final class JobAdvertTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['job-advert'];
    protected $viewModelClasses = [Teaser::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new JobAdvertTeaserConverter($this->stubUrlGenerator());
    }
}
