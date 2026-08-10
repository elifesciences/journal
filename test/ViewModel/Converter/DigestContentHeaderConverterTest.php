<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\DigestContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNew;
use PHPUnit\Framework\Attributes\Before;

final class DigestContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['digest'];
    protected $viewModelClasses = [ContentHeaderNew::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new DigestContentHeaderConverter($this->stubUrlGenerator());
    }
}
