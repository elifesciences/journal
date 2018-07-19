<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\DigestContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeader;

final class DigestContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['digest'];
    protected $viewModelClasses = [ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new DigestContentHeaderConverter($this->stubUrlGenerator());
    }
}
