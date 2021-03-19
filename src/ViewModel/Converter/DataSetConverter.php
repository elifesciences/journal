<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\DataSet;
use eLife\Journal\ViewModel\Converter\Reference\HasAuthors;
use eLife\Patterns\ViewModel;

final class DataSetConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param DataSet $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = [];
        if ($object->getDataId()) {
            $origin[] = 'ID '.$object->getDataId();
        }
        if ($object->getDetails()) {
            $origin[] = rtrim($object->getDetails(), '.');
        }

        $authors = [$this->createAuthors($object->getAuthors(), $object->authorsEtAl(), [$object->getDate()->format()])];

        if ($object->getDoi()) {
            return ViewModel\Reference::withDoi($object->getTitle(), new ViewModel\Doi($object->getDoi()), $object->getId(), null, $origin, $authors);
        }

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getTitle(), $object->getUri()), $object->getId(), null, $origin, $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof DataSet;
    }
}
