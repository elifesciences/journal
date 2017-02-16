<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Patterns\ViewModel;

final class LabsExperimentContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    /**
     * @param LabsExperiment $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\ContentHeaderNonArticle::basic($object->getTitle(), false, null, null,
            ViewModel\Meta::withText('Experiment: '.str_pad($object->getNumber(), 3, '0', STR_PAD_LEFT), $this->simpleDate($object, $context)),
            new ViewModel\BackgroundImage(
                $object->getBanner()->getSize('2:1')->getImage(900),
                $object->getBanner()->getSize('2:1')->getImage(1800)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof LabsExperiment && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
