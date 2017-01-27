<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PreprintReference;
use eLife\Journal\Helper\Callback;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class PreprintReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param PreprintReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), [$object->getDate()->format().$object->getDiscriminator()])];

        if ($object->getDoi()) {
            return ViewModel\Reference::withDoi($object->getArticleTitle(), new ViewModel\Doi($object->getDoi()), [$object->getSource()], $authors);
        }

        $query = [
            'title' => strip_tags($object->getArticleTitle()),
            'author' => array_map(Callback::method('toString'), $object->getAuthors()),
            'publication_year' => $object->getDate()->getYear(),
        ];

        $abstracts = [new ViewModel\Link('Google Scholar', 'https://scholar.google.com/scholar_lookup?'.str_replace(['%5B0%5D=', '%5B1%5D='], '=', http_build_query($query)))];

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getArticleTitle(), $object->getUri()), [$object->getSource()], $authors, $abstracts);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PreprintReference;
    }
}
