<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\JobAdvertContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNew;
use PHPUnit\Framework\Attributes\Before;

final class JobAdvertContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['job-advert'];
    protected $viewModelClasses = [ContentHeaderNew::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new JobAdvertContentHeaderConverter($this->stubUrlGenerator());
    }
}
