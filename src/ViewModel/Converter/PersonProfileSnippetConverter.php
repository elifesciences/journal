<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Person;
use eLife\Journal\Helper\PersonType;
use eLife\Patterns\ViewModel;

final class PersonProfileSnippetConverter implements ViewModelConverter
{
    /**
     * @param Person $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($object->getThumbnail()) {
            $image = new ViewModel\Image(
                $object->getThumbnail()->getSize('1:1')->getImage(70),
                [
                    140 => $object->getThumbnail()->getSize('1:1')->getImage(140),
                    70 => $object->getThumbnail()->getSize('1:1')->getImage(70),
                ],
                $object->getThumbnail()->getAltText()
            );
        } else {
            $image = null;
        }

        return new ViewModel\ProfileSnippet(
            $object->getDetails()->getPreferredName(),
            PersonType::singular($object->getType()),
            $image
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Person && ViewModel\ProfileSnippet::class === $viewModel;
    }
}
