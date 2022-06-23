<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\DigestContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNew;

final class DigestContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['digest'];
    protected $viewModelClasses = [ContentHeaderNew::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new DigestContentHeaderConverter($this->stubUrlGenerator());
    }
}
