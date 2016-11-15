<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PreprintReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class PreprintReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param PreprintReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), [$object->getDate()->format().$object->getDiscriminator()])];

        if ($object->getDoi()) {
            return ViewModel\Reference::withDoi($object->getArticleTitle(), new ViewModel\Doi($object->getDoi()), [$object->getSource()], $authors);
        }

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getArticleTitle(), $object->getUri()), [$object->getSource()], $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PreprintReference;
    }
}
