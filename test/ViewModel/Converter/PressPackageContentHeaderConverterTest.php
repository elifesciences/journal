<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PressPackageContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeader;

final class PressPackageContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['press-package'];
    protected $viewModelClasses = [ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PressPackageContentHeaderConverter($this->stubUrlGenerator());
    }
}
