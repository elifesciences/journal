<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ConferenceProceedingReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class ConferenceProceedingReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param ConferenceProceedingReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = [$object->getConference()->toString()];
        if ($object->getPages()) {
            $origin[] = $object->getPages()->toString();
        }

        $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), [$object->getDate()->format().$object->getDiscriminator()])];

        if ($object->getDoi()) {
            return ViewModel\Reference::withDoi($object->getArticleTitle(), new ViewModel\Doi($object->getDoi()), $origin, $authors);
        }

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getArticleTitle(), $object->getUri()), $origin, $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ConferenceProceedingReference;
    }
}
