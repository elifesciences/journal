<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PressPackage;
use eLife\Journal\ViewModel\Converter\PressPackageContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;

final class PressPackageContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['press-package'];
    protected $class = PressPackage::class;
    protected $viewModelClass = ContentHeaderNonArticle::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PressPackageContentHeaderConverter($this->stubUrlGenerator());
    }
}
