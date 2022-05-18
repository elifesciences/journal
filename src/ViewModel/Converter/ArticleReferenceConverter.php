<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\Journal\ViewModel\Converter\Reference\HasAuthors;
use eLife\Patterns\ViewModel;

final class ArticleReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = ['<i>eLife</i> <b>'.$object->getVolume().'</b>:'.$object->getElocationId()];

        $authors = $object->getAuthors()->notEmpty() ? [new ViewModel\ReferenceAuthorList($object->getAuthors()->map(function (AuthorEntry $author) {
            return ViewModel\Author::asText($author->toString());
        })->toArray(), '('.($object->getPublishedDate() ? $object->getPublishedDate()->format('Y') : '').')')] : [];

        return ViewModel\Reference::withDoi($object->getFullTitle(), new ViewModel\Doi($object->getDoi(), true), null, null, $origin, $authors, []);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\Reference::class === $viewModel;
    }
}
