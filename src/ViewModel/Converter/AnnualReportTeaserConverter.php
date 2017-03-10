<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\AnnualReport;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Teaser;

final class AnnualReportTeaserConverter implements ViewModelConverter
{
    /**
     * @param AnnualReport $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return Teaser::main(
            $object->getTitle(),
            $object->getUri(),
            $object->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof AnnualReport && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
