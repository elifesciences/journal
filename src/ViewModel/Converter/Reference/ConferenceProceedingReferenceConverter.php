<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ConferenceProceedingReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class ConferenceProceedingReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param ConferenceProceedingReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = [
            $object->getDate()->format().$object->getDiscriminator(),
            $object->getConference()->toString(),
        ];
        if ($object->getPages()) {
            $origin[] = $object->getPages()->toString();
        }

        return new ViewModel\Reference(
            $object->getArticleTitle(),
            implode('. ', $origin).'.',
            $object->getUri(),
            $object->getDoi() ? $object->getUri() : null,
            $this->createAuthors($object->getAuthors(), $object->authorsEtAl())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ConferenceProceedingReference;
    }
}
