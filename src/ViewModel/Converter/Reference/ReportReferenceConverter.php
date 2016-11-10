<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ReportReference;
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

        return new ViewModel\Reference(
            $object->getTitle(),
            implode('. ', $origin).'.',
            $object->getUri(),
            $object->getDoi() ? $object->getUri() : null,
            $this->createAuthors($object->getAuthors(), $object->authorsEtAl())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ReportReference;
    }
}
