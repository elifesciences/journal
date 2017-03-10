<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\AnnualReport;
use eLife\Journal\ViewModel\Converter\AnnualReportTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class AnnualReportTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['annual-report'];
    protected $class = AnnualReport::class;
    protected $viewModelClass = Teaser::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new AnnualReportTeaserConverter();
    }
}
