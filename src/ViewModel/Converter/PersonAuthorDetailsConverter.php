<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\Journal\Helper\Callback;
use eLife\Patterns\ViewModel;

final class PersonAuthorDetailsConverter implements ViewModelConverter
{
    /**
     * @param PersonAuthor $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\AuthorDetails(
            'author-'.hash('crc32', $object->toString()),
            $object->toString(),
            array_map(Callback::method('toString'), $object->getAffiliations()),
            array_map(Callback::method('toString'), $object->getPostalAddresses()),
            $object->getContribution(),
            ($context['authors'] ?? false) ? $this->findEqualContributions($object, $context['authors']) : null,
            $object->getEmailAddresses(),
            $object->getPhoneNumbers(),
            $object->getCompetingInterests() ?? 'No competing interests declared.',
            $object->getOrcid()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PersonAuthor;
    }

    private function findEqualContributions(PersonAuthor $author, Sequence $authors)
    {
        $authors = $authors->filter(function (Author $otherAuthor) use ($author) {
            if ($otherAuthor == $author || false === $otherAuthor instanceof Author) {
                return false;
            }

            return (bool) count(array_intersect($author->getEqualContributionGroups(), $otherAuthor->getEqualContributionGroups()));
        })->map(function (Author $authorEntry) {
            return $authorEntry->toString();
        });

        if ($authors->isEmpty()) {
            return null;
        }

        return $this->prettyList(...$authors);
    }

    private function prettyList(string ...$items) : string
    {
        $last = array_slice($items, -1);
        $first = implode(', ', array_slice($items, 0, -1));
        $both = array_filter(array_merge([$first], $last), 'strlen');

        return implode(' and ', $both);
    }
}
