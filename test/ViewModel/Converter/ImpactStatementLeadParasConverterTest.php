<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\ImpactStatementLeadParasConverter;
use eLife\Patterns\ViewModel\LeadParas;
use Traversable;

final class ImpactStatementLeadParasConverterTest extends ModelConverterTestCase
{
    protected $models = ['annual-report', 'article-vor', 'blog-article', 'collection', 'event', 'interview', 'labs-experiment', 'medium-article', 'podcast-episode', 'press-package', 'subject'];
    protected $viewModelClasses = [LeadParas::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ImpactStatementLeadParasConverter();
    }

    /**
     * @param HasImpactStatement $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getImpactStatement()) {
            yield $model;
        }
    }
}
