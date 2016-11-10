<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\BookChapterReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class BookChapterReferenceConverter implements ViewModelConverter
{
    use HasAuthors;
    use HasPublisher;

    /**
     * @param BookChapterReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $bookTitle = $object->getChapterTitle();
        if ($object->getVolume()) {
            $bookTitle .= ', '.$object->getVolume();
        }
        if ($object->getEdition()) {
            $bookTitle .= ' ('.$object->getEdition().')';
        }

        $origin = [
            $object->getDate()->format().$object->getDiscriminator(),
            'In: '.$this->createAuthorsString($object->getEditors(), $object->editorsEtAl()).', editors',
            '<i>'.$bookTitle.'</i>',
            $this->publisherToString($object->getPublisher()),
        ];
        if ($object->getPmid()) {
            $origin[] = 'PMID '.$object->getPmid();
        }
        if ($object->getIsbn()) {
            $origin[] = 'ISBN '.$object->getIsbn();
        }
        $origin[] = $object->getPages()->toString();

        $doiUri = ($object->getDoi() ? 'https://doi.org/'.$object->getDoi() : null);

        return new ViewModel\Reference(
            $object->getChapterTitle(),
            implode('. ', $origin).'.',
            $doiUri,
            $doiUri,
            $this->createAuthors($object->getAuthors(), $object->authorsEtAl())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BookChapterReference;
    }
}
