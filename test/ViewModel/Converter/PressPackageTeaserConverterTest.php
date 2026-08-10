<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PressPackageTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use PHPUnit\Framework\Attributes\Before;

final class PressPackageTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['press-package'];
    protected $viewModelClasses = [Teaser::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new PressPackageTeaserConverter($this->stubUrlGenerator());
    }
}
