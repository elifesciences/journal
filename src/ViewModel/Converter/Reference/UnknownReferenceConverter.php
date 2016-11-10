<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\UnknownReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class UnknownReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param UnknownReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = [$object->getDate()->format().$object->getDiscriminator()];
        if ($object->getDetails()) {
            $origin[] = $object->getDetails();
        }

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
        return $object instanceof UnknownReference;
    }
}
