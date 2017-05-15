<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel;

final class LabsExperimentContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    /**
     * @param LabsExperiment $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ContentHeader(
            $object->getTitle(),
            null, $object->getImpactStatement(), false, [], null, null, [], [], null, null, null,
            ViewModel\Meta::withText(
                'Experiment: '.str_pad($object->getNumber(), 3, '0', STR_PAD_LEFT),
                $this->simpleDate($object, $context)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof LabsExperiment && ViewModel\ContentHeader::class === $viewModel;
    }
}
