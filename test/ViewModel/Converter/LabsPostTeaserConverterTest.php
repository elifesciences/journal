<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\LabsPostTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class LabsPostTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-post'];
    protected $viewModelClasses = [Teaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new LabsPostTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }
}
