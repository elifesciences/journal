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
        $origin = [
            $object->getDate()->format(),
            $object->getPublisher()->toString(),
        ];

        return new ViewModel\Reference(
            $object->getTitle(),
            implode('. ', $origin).'.',
            $object->getUri(),
            $object->getDoi() ? $object->getUri() : null,
            [ViewModel\Author::asText($object->getAuthor()->getPreferredName())]
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ThesisReference;
    }
}
