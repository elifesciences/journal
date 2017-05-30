<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\LabsPostSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class LabsPostSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-post'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new LabsPostSecondaryTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }
}
