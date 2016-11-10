<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\BookReference;
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

        $origin = [
            $object->getDate()->format().$object->getDiscriminator(),
            $this->publisherToString($object->getPublisher()),
        ];
        if ($object->getPmid()) {
            $origin[] = 'PMID '.$object->getPmid();
        }
        if ($object->getIsbn()) {
            $origin[] = 'ISBN '.$object->getIsbn();
        }

        $doiUri = ($object->getDoi() ? 'https://doi.org/'.$object->getDoi() : null);

        return new ViewModel\Reference(
            $title,
            implode('. ', $origin).'.',
            $doiUri,
            $doiUri,
            $this->createAuthors($object->getAuthors(), $object->authorsEtAl())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BookReference;
    }
}
