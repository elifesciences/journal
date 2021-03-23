<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PeriodicalReference;
use eLife\ApiSdk\Model\Reference\ReferencePageRange;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class PeriodicalReferenceConverter implements ViewModelConverter
{
    const LABEL = 'Periodical';

    use HasAuthors;
    use HasLabel;

    /**
     * @param PeriodicalReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $periodical = '<i>'.$object->getPeriodical().'</i>';
        if ($object->getVolume()) {
            $periodical .= ' <b>'.$object->getVolume().'</b>:';
            if ($object->getPages() instanceof ReferencePageRange) {
                $periodical .= $object->getPages()->getRange();
            } else {
                $periodical .= $object->getPages()->toString();
            }
        } elseif ($object->getPages()) {
            $periodical .= ' '.$object->getPages()->toString();
        }

        $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), [$object->getDate()->format().$object->getDiscriminator()])];

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getArticleTitle(), $object->getUri()), $object->getId(), $this->label(), [$periodical], $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PeriodicalReference;
    }
}
