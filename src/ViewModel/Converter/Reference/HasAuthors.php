<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\Patterns\ViewModel;

trait HasAuthors
{
    private function createAuthors(array $authors, bool $etAl, array $suffixes) : ViewModel\ReferenceAuthorList
    {
        $authors = array_map(function (Model\AuthorEntry $author) {
            return ViewModel\Author::asLink(new ViewModel\Link($author->toString(), 'https://scholar.google.com/scholar?q=%22author:'.urlencode($author->toString()).'%22'));
        }, $authors);

        $suffix = trim(array_reduce(array_filter($suffixes), function (string $carry, string $suffix) {
            return $carry.' ('.$suffix.')';
        }, ''));

        if ($etAl) {
            $suffix = 'et al '.$suffix;
        }

        return new ViewModel\ReferenceAuthorList($authors, $suffix);
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
