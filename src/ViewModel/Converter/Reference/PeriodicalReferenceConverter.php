<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PeriodicalReference;
use eLife\ApiSdk\Model\Reference\ReferencePageRange;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class PeriodicalReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param PeriodicalReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $periodical = '<i>'.implode(', ', $object->getPeriodical()->getName()).'</i>';
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

        $origin = [
            $object->getDate()->format(),
            $periodical,
        ];

        return new ViewModel\Reference(
            $object->getArticleTitle(),
            implode('. ', $origin).'.',
            $object->getUri(),
            null,
            $this->createAuthors($object->getAuthors(), $object->authorsEtAl())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PeriodicalReference;
    }
}
