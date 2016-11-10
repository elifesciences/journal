<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PatentReference;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class PatentReferenceConverter implements ViewModelConverter
{
    use HasAuthors;

    /**
     * @param PatentReference $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $title = $object->getTitle();
        if ($object->getNumber()) {
            $title .= ' ('.$object->getNumber().')';
        }

        $origin = [$object->getDate()->format().$object->getDiscriminator()];
        if ($object->getAssignees()) {
            $origin[] = $this->createAuthorsString($object->getAssignees(), $object->assigneesEtAl());
        }
        $origin[] = $object->getPatentType();
        $origin[] = $object->getCountry();

        return new ViewModel\Reference(
            $title,
            implode('. ', $origin).'.',
            $object->getUri(),
            null,
            $this->createAuthors($object->getInventors(), $object->inventorsEtAl())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PatentReference;
    }
}
