<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Person;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Patterns\ViewModel;

final class PersonProfileSnippetConverter implements ViewModelConverter
{
    use CreatesIiifUri;

    /**
     * @param Person $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($object->getThumbnail()) {
            $image = new ViewModel\Image(
                $this->iiifUri($object->getThumbnail(), 70, 70),
                $this->iiifUri($object->getThumbnail(), 140, 140),
                $object->getThumbnail()->getAltText()
            );
        } else {
            $image = null;
        }

        return new ViewModel\ProfileSnippet(
            $object->getDetails()->getPreferredName(),
            $object->getTypeLabel(),
            $image
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Person && ViewModel\ProfileSnippet::class === $viewModel;
    }
}
