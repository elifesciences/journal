<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\DataReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class DataReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param DataReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = [
            $object->getDate()->format().$object->getDiscriminator(),
            $object->getSource(),
        ];
        if ($object->getAssigningAuthority()) {
            $origin[] = $object->getAssigningAuthority()->toString();
        }
        if ($object->getDataId()) {
            $origin[] = 'ID '.$object->getDataId();
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
        return $object instanceof DataReference;
    }
}
