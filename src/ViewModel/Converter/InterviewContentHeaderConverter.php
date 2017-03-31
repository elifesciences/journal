<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Patterns\ViewModel;

final class InterviewContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    /**
     * @param Interview $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\ContentHeaderNonArticle::basic(
            $object->getTitle(),
            false,
            null,
            null,
            ViewModel\Meta::withText('Interview', $this->simpleDate($object, ['date' => 'published'] + $context))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Interview && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
