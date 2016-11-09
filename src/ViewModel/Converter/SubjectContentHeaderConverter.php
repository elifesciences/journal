<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Subject;
use eLife\Patterns\ViewModel;

final class SubjectContentHeaderConverter implements ViewModelConverter
{
    /**
     * @param Subject $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\ContentHeaderNonArticle::subject($object->getName(), false, null,
            new ViewModel\BackgroundImage(
                $object->getBanner()->getSizes()[0]->getImage(900),
                $object->getBanner()->getSizes()[0]->getImage(1800)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Subject && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
