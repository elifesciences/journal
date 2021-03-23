<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ClinicalTrialReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class ClinicalTrialReferenceConverter implements ViewModelConverter
{
    const LABEL = 'Clinical Trial';

    use HasAuthors;
    use HasLabel;

    /**
     * @param ClinicalTrialReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), [$object->getAuthorsType(), $object->getDate()->format().$object->getDiscriminator()])];

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getTitle(), $object->getUri()), $object->getId(), $this->label(), [], $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ClinicalTrialReference;
    }
}
