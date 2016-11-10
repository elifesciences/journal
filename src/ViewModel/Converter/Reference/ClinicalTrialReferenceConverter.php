<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ClinicalTrialReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class ClinicalTrialReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param ClinicalTrialReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = [$object->getDate()->format().$object->getDiscriminator()];

        return new ViewModel\Reference(
            $object->getTitle(),
            implode('. ', $origin).'.',
            $object->getUri(),
            null,
            $this->createAuthors($object->getAuthors(), $object->authorsEtAl())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ClinicalTrialReference;
    }
}
