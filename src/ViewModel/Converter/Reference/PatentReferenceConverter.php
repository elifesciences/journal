<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PatentReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class PatentReferenceConverter implements ViewModelConverter
{
    const LABEL = 'Patent';

    use HasAuthors;
    use HasLabel;

    /**
     * @param PatentReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $title = $object->getTitle();
        if ($object->getNumber()) {
            $title .= ' ('.$object->getNumber().')';
        }

        $origin = [];
        if ($object->getAssignees()) {
            $origin[] = $this->createAuthorsString($object->getAssignees(), $object->assigneesEtAl());
        }
        $origin[] = $object->getPatentType();
        $origin[] = $object->getCountry();

        $authors = [$this->createAuthors($object->getInventors(), $object->inventorsEtAl(), [$object->getDate()->format().$object->getDiscriminator()])];

        return ViewModel\Reference::withOutDoi(new ViewModel\Link($title, $object->getUri()), $object->getId(), $this->label(), $origin, $authors);
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PatentReference;
    }
}
