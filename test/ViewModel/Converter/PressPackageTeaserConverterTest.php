<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PressPackage;
use eLife\Journal\ViewModel\Converter\PressPackageTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class PressPackageTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['press-package'];
    protected $class = PressPackage::class;
    protected $viewModelClass = Teaser::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PressPackageTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }
}
