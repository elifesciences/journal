<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\Journal\ViewModel\Converter\ImpactStatementLeadParasConverter;
use eLife\Patterns\ViewModel\LeadParas;

final class ImpactStatementLeadParasConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-vor'];
    protected $class = ArticleVersion::class;
    protected $viewModelClass = LeadParas::class;
    protected $selectSamples = ['complete.json'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ImpactStatementLeadParasConverter();
    }
}
