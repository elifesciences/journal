<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\DataReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class DataReferenceConverter implements ViewModelConverter
{
    const LABEL = 'Data';

    use HasAuthors;
    use HasLabel;

    /**
     * @param DataReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $origin = [$object->getSource()];
        if ($object->getAssigningAuthority()) {
            $origin[] = $object->getAssigningAuthority()->toString();
        }
        if ($object->getDataId()) {
            $origin[] = 'ID '.$object->getDataId();
        }

        $authors = [];
        $year = true;
        if ($object->getCurators()) {
            $authors[] = $this->createAuthors($object->getCurators(), $object->curatorsEtAl(), ['curators', $object->getDate()->format().$object->getDiscriminator()]);
            $year = false;
        }
        if ($object->getCompilers()) {
            array_unshift($authors, $this->createAuthors($object->getCompilers(), $object->compilersEtAl(), ['compilers', $year ? $object->getDate()->format().$object->getDiscriminator() : '']));
            $year = false;
        }
        if ($object->getAuthors()) {
            array_unshift($authors, $this->createAuthors($object->getAuthors(), $object->authorsEtAl(), ['authors', $year ? $object->getDate()->format().$object->getDiscriminator() : '']));
        }

        if ($object->getDoi()) {
            return ViewModel\Reference::withDoi($object->getTitle(), new ViewModel\Doi($object->getDoi()), $object->getId(), $this->label(), $origin, $authors);
        }

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($object->getTitle(), $object->getUri()), $object->getId(), $this->label(), $origin, $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof DataReference;
    }
}
