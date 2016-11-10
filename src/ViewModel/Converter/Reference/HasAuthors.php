<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\Patterns\ViewModel;
use UnexpectedValueException;

trait HasAuthors
{
    private function createAuthors(array $authors, bool $etAl = false) : array
    {
        $authors = array_map(function (Model\AuthorEntry $author) {
            return ViewModel\Author::asText($this->authorToString($author));
        }, $authors);

        if ($etAl) {
            $authors[] = ViewModel\Author::asText('et al');
        }

        return $authors;
    }

    private function createAuthorsString(array $authors, bool $etAl = false) : string
    {
        $authors = implode(', ', array_map(function (AuthorEntry $author) {
            return $this->authorToString($author);
        }, $authors));

        if ($etAl) {
            $authors .= ' et al';
        }

        return $authors;
    }

    private function authorToString(Model\AuthorEntry $author) : string
    {
        if ($author instanceof Model\PersonAuthor) {
            return $author->getPreferredName();
        } elseif ($author instanceof Model\GroupAuthor) {
            return $author->getName();
        } elseif ($author instanceof Model\OnBehalfOfAuthor) {
            return $author->getOnBehalfOf();
        }

        throw new UnexpectedValueException('Unknown author type '.get_class($author));
    }
}
