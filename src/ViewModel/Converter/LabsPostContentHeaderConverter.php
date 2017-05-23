<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsPost;
use eLife\Patterns\ViewModel;

final class LabsPostContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    /**
     * @param LabsPost $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ContentHeader(
            $object->getTitle(),
            null, $object->getImpactStatement(), false, [], null, null, [], [], null, null, null,
            ViewModel\Meta::withText(
                'Post: '.str_pad($object->getId(), 3, '0', STR_PAD_LEFT),
                $this->simpleDate($object, $context)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof LabsPost && ViewModel\ContentHeader::class === $viewModel;
    }
}
