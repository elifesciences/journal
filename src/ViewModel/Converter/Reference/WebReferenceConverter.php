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
        $origin = [$object->getDate()->format()];
        if ($object->getWebsite()) {
            $origin[] = $object->getWebsite();
        }
        if ($object->getAccessed()) {
            $origin[] = 'Accessed '.$object->getAccessed()->format();
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
        return $object instanceof WebReference;
    }
}
