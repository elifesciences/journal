<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Patterns\ViewModel;

final class SubjectContentHeaderConverter implements ViewModelConverter
{
    use CreatesIiifUri;

    /**
     * @param Subject $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\ContentHeaderNonArticle::subject($object->getName(), false, null,
            new ViewModel\BackgroundImage(
                $this->iiifUri($object->getBanner(), 900, 450),
                $this->iiifUri($object->getBanner(), 1800, 900)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Subject && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
