<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Author;
use eLife\Journal\Helper\Callback;

trait AuthorDetailsConverter
{
    private function findDetails(Author $author, Sequence $authors) : array
    {
        return array_filter(
            [
                '' => array_map(Callback::method('toString'), $author->getAffiliations()),
                'Present address' => array_map(Callback::method('toString'), $author->getPostalAddresses()),
                'Contribution' => $author->getContribution(),
                'Contributed equally with' => $this->findEqualContributions($author, $authors),
                'For correspondence' => array_merge(
                    array_map(function (string $emailAddress) {
                        return "<a href=\"mailto:$emailAddress\">$emailAddress</a>";
                    }, $author->getEmailAddresses()),
                    array_map(function (string $phoneNumber) {
                        return "<a href=\"tel:$phoneNumber\">$phoneNumber</a>";
                    }, $author->getPhoneNumbers())
                ),
                'Competing interests' => $author->getCompetingInterests() ?? 'No competing interests declared.',
                'Additional information' => $author->getAdditionalInformation(),
            ]
        );
    }

    /**
     * @return string|null
     */
    private function findEqualContributions(Author $author, Sequence $authors)
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
