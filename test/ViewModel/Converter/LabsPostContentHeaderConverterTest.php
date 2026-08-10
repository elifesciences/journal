<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\LabsPostContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeader;
use PHPUnit\Framework\Attributes\Before;

final class LabsPostContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-post'];
    protected $viewModelClasses = [ContentHeader::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new LabsPostContentHeaderConverter($this->stubUrlGenerator());
    }
}
