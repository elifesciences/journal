<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\Journal\Helper\Callback;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class BookReferenceConverter implements ViewModelConverter
{
    use HasAuthors;
    use HasPublisher;

    /**
     * @param BookReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $title = $object->getBookTitle();
        if ($object->getVolume()) {
            $title .= ', '.$object->getVolume();
        }
        if ($object->getEdition()) {
            $title .= ' ('.$object->getEdition().')';
        }

        $origin = [];

        $abstracts = [];
        if ($object->getPmid()) {
            $abstracts[] = new ViewModel\Link('PubMed', 'https://www.ncbi.nlm.nih.gov/pubmed/'.$object->getPmid());
        }

        $query = [
            'title' => strip_tags($object->getBookTitle()),
            'author' => array_map(Callback::method('toString'), $object->getAuthors()),
            'publication_year' => $object->getDate()->getYear(),
            'pmid' => $object->getPmid(),
            'isbn' => $object->getIsbn(),
        ];

        $abstracts[] = new ViewModel\Link('Google Scholar', 'https://scholar.google.com/scholar_lookup?'.str_replace(['%5B0%5D=', '%5B1%5D='], '=', http_build_query($query)));

        $authorsSuffix = [$object->getDate()->format().$object->getDiscriminator()];

        if (empty($object->getAuthors())) {
            $authors = [$this->createAuthors($object->getEditors(), $object->editorsEtAl(), array_merge(['editors'], $authorsSuffix))];
        } else {
            $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), $authorsSuffix)];
            if (!empty($object->getEditors())) {
                $origin[] = $this->createAuthorsString($object->getEditors(), $object->editorsEtAl()).', editors';
            }
        }

        $origin[] = $this->publisherToString($object->getPublisher());
        if ($object->getIsbn()) {
            $origin[] = 'ISBN '.$object->getIsbn();
        }

        if ($object->getDoi()) {
            return ViewModel\Reference::withDoi($title, new ViewModel\Doi($object->getDoi()), $object->getId(), $origin, $authors, $abstracts);
        }

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($title), $object->getId(), $origin, $authors, $abstracts);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BookReference;
    }
}
