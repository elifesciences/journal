<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ThesisReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class ThesisReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param ThesisReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $authors = [new ViewModel\ReferenceAuthorList([ViewModel\Author::asText($object->getAuthor()->getPreferredName())], '('.$object->getDate()->format().$object->getDiscriminator().')')];

        if ($object->getDoi()) {
            return ViewModel\Reference::withDoi($object->getTitle(), new ViewModel\Doi($object->getDoi()), [$object->getPublisher()->toString()], $authors);
        }

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getTitle(), $object->getUri()), [$object->getPublisher()->toString()], $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ThesisReference;
    }
}
