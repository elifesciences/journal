<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PressPackageContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeader;
use PHPUnit\Framework\Attributes\Before;

final class PressPackageContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['press-package'];
    protected $viewModelClasses = [ContentHeader::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new PressPackageContentHeaderConverter($this->stubUrlGenerator());
    }
}
