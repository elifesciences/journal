<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\WebReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class WebReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param WebReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = [];
        if ($object->getWebsite()) {
            $origin[] = $object->getWebsite();
        }
        if ($object->getAccessed()) {
            $origin[] = 'Accessed '.$object->getAccessed()->format();
        }

        $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), [$object->getDate()->format().$object->getDiscriminator()])];

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getTitle(), $object->getUri()), $object->getId(), $origin, $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof WebReference;
    }
}
