<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\LabsPostGridTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class LabsPostGridTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-post'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'grid'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new LabsPostGridTeaserConverter($this->stubUrlGenerator());
    }
}
