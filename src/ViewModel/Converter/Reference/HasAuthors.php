<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\Patterns\ViewModel;

trait HasAuthors
{
    private function createAuthors(array $authors, bool $etAl = false) : array
    {
        $authors = array_map(function (Model\AuthorEntry $author) {
            return ViewModel\Author::asText($author->toString());
        }, $authors);

        if ($etAl) {
            $authors[] = ViewModel\Author::asText('et al');
        }

        return $authors;
    }

    private function createAuthorsString(array $authors, bool $etAl = false) : string
    {
        $authors = implode(', ', array_map(function (AuthorEntry $author) {
            return $author->toString();
        }, $authors));

        if ($etAl) {
            $authors .= ' et al';
        }

        return $authors;
    }
}
