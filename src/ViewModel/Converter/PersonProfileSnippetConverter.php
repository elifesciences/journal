<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Person;
use eLife\Patterns\ViewModel;

final class PersonProfileSnippetConverter implements ViewModelConverter
{
    private $viewModelConverter;

    public function __construct(ViewModelConverter $viewModelConverter)
    {
        $this->viewModelConverter = $viewModelConverter;
    }

    /**
     * @param Person $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($object->getThumbnail()) {
            $image = $this->viewModelConverter->convert($object->getThumbnail(), null, ['width' => 70, 'height' => 70]);
        } else {
            $image = null;
        }

        if ('institution' === ($context['title'] ?? null) && ($object->getAffiliations()->notEmpty())) {
            $title = $object->getAffiliations()[0]->toString();
        }

        return new ViewModel\ProfileSnippet(
            $object->getDetails()->getPreferredName(),
            $title ?? $object->getTypeLabel(),
            $image
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Person && ViewModel\ProfileSnippet::class === $viewModel;
    }
}
