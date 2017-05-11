<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\Journal\Helper\Callback;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AuthorDetails;

final class GroupAuthorDetailsConverter implements ViewModelConverter
{
    use AuthorDetailsConverter;
    use CreatesId;

    /**
     * @param GroupAuthor $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return AuthorDetails::forGroup(
            $this->createId($object),
            $object->toString(),
            $this->findDetails($object, $context['authors'] ?? new EmptySequence()),
            array_filter(
                [
                    '' => $object->getPeople()->map(function (PersonAuthor $author) {
                        return implode(', ', array_filter([$author->toString(), !empty($author->getAffiliations()) ? $author->getAffiliations()[0]->toString() : null]));
                    })->toArray(),
                ]
                +
                array_map(
                    function (array $items) {
                        return array_map(Callback::method('getPreferredName'), $items);
                    },
                    $object->getGroups()
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof GroupAuthor;
    }
}
