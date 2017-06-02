<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ReportReference;
use eLife\Journal\Helper\Callback;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class ReportReferenceConverter implements ViewModelConverter
{
    use HasAuthors;
    use HasPublisher;

    /**
     * @param ReportReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = [$this->publisherToString($object->getPublisher())];
        if ($object->getIsbn()) {
            $origin[] = 'ISBN '.$object->getIsbn();
        }

        $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), [$object->getDate()->format().$object->getDiscriminator()])];

        $abstracts = [];
        if ($object->getPmid()) {
            $abstracts[] = new ViewModel\Link('PubMed', 'https://www.ncbi.nlm.nih.gov/pubmed/'.$object->getPmid());
        }

        $query = [
            'title' => strip_tags($object->getTitle()),
            'author' => array_map(Callback::method('toString'), $object->getAuthors()),
            'publication_year' => $object->getDate()->getYear(),
            'pmid' => $object->getPmid(),
            'isbn' => $object->getIsbn(),
        ];

        $abstracts[] = new ViewModel\Link('Google Scholar', 'https://scholar.google.com/scholar_lookup?'.str_replace(['%5B0%5D=', '%5B1%5D='], '=', http_build_query($query)));

        if ($object->getDoi()) {
            return ViewModel\Reference::withDoi($object->getTitle(), new ViewModel\Doi($object->getDoi()), $origin, $authors, $abstracts);
        }

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getTitle(), $object->getUri()), $origin, $authors, $abstracts);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ReportReference;
    }
}
