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

        $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), [$object->getDate()->format().$object->getDiscriminator()])];

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($title, $object->getUri()), $object->getId(), [$object->getPublisher()->toString()], $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof SoftwareReference;
    }
}
