<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
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
            array_map(function (Place $affiliation) {
                return $affiliation->toString();
            }, $object->getAffiliations()),
            array_map(function (Address $address) {
                return $address->getFormatted();
            }, $object->getPostalAddresses()),
            $object->getContribution(),
            ($context['article'] ?? false) ? $this->findEqualContributions($object, $context['article']) : null,
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

    private function findEqualContributions(PersonAuthor $author, ArticleVersion $article)
    {
        $authors = $article->getAuthors()->filter(function (Author $otherAuthor) use ($author) {
            if ($otherAuthor == $author || false === $otherAuthor instanceof Author) {
                return false;
            }

            return (bool) count(array_intersect($author->getEqualContributionGroups(), $otherAuthor->getEqualContributionGroups()));
        })->map(function (Author $authorEntry) {
            return $authorEntry->toString();
        })->toArray();

        if (empty($authors)) {
            return null;
        }

        return $this->prettyList($authors);
    }

    private function prettyList(array $items) : string
    {
        $last = array_slice($items, -1);
        $first = join(', ', array_slice($items, 0, -1));
        $both = array_filter(array_merge([$first], $last), 'strlen');

        return join(' and ', $both);
    }
}
