<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\LeadPara;
use eLife\Patterns\ViewModel\LeadParas;

final class ImpactStatementLeadParasConverter implements ViewModelConverter
{
    /**
     * @param HasImpactStatement $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new LeadParas([new LeadPara($object->getImpactStatement())]);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof HasImpactStatement && ViewModel\LeadParas::class === $viewModel && !empty($object->getImpactStatement());
    }
}
