<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\JournalReference;
use eLife\ApiSdk\Model\Reference\ReferencePageRange;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class JournalReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param JournalReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $journal = '<i>'.implode(', ', $object->getJournal()->getName()).'</i>';
        if ($object->getVolume()) {
            $journal .= ' <b>'.$object->getVolume().'</b>:';
            if ($object->getPages() instanceof ReferencePageRange) {
                $journal .= $object->getPages()->getRange();
            } else {
                $journal .= $object->getPages()->toString();
            }
        } elseif ($object->getPages()) {
            $journal .= ' '.$object->getPages()->toString();
        }

        $origin = [
            $object->getDate()->format().$object->getDiscriminator(),
            $journal,
        ];
        if ($object->getPmid()) {
            $origin[] = 'PMID '.$object->getPmid();
        }

        $doiUrl = $object->getDoi() ? 'https://doi.org/'.$object->getDoi() : null;

        return new ViewModel\Reference(
            $object->getArticleTitle(),
            implode('. ', $origin).'.',
            $doiUrl,
            $doiUrl,
            $this->createAuthors($object->getAuthors(), $object->authorsEtAl())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof JournalReference;
    }
}
