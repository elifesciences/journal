<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AuthorDetails;

final class PersonAuthorDetailsConverter implements ViewModelConverter
{
    use AuthorDetailsConverter;

    /**
     * @param PersonAuthor $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return AuthorDetails::forPerson(
            'author-'.hash('crc32', $object->toString()),
            $object->toString(),
            $this->findDetails($object, $context['authors'] ?? new EmptySequence()),
            $object->getOrcid()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PersonAuthor;
    }
}
