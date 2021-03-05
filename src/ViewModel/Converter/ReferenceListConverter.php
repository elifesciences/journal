<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\HasReferences;
use eLife\ApiSdk\Model\Reference;
use eLife\Patterns\ViewModel;

final class ReferenceListConverter implements ViewModelConverter
{
    private $viewModelConverter;

    public function __construct(ViewModelConverter $viewModelConverter)
    {
        $this->viewModelConverter = $viewModelConverter;
    }

    /**
     * @param HasReferences $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ReferenceList(
            ...$object->getReferences()
            ->map(function (Reference $reference) {
                return $this->viewModelConverter->convert($reference);
            })
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof HasReferences && ViewModel\ReferenceList::class === $viewModel && $object->getReferences()->notEmpty();
    }
}
