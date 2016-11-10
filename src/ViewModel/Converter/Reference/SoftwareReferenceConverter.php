<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\SoftwareReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class SoftwareReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param SoftwareReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $title = $object->getTitle();
        if ($object->getVersion()) {
            $title .= ', version '.$object->getVersion();
        }

        $origin = [
            $object->getDate()->format(),
            $object->getPublisher()->toString(),
        ];

        return new ViewModel\Reference(
            $title,
            implode('. ', $origin).'.',
            $object->getUri(),
            null,
            $this->createAuthors($object->getAuthors(), $object->authorsEtAl())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof SoftwareReference;
    }
}
