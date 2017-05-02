<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel;

final class SubjectContentHeaderConverter implements ViewModelConverter
{
    private $contentHeaderImageFactory;

    public function __construct(ContentHeaderImageFactory $contentHeaderImageFactory)
    {
        $this->contentHeaderImageFactory = $contentHeaderImageFactory;
    }

    /**
     * @param Subject $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ContentHeader(
            $object->getName(),
            $this->contentHeaderImageFactory->forImage($object->getBanner()),
            $object->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Subject && ViewModel\ContentHeader::class === $viewModel;
    }
}
