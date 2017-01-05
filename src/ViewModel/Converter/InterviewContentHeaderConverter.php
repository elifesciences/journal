<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Patterns\ViewModel;
use Symfony\Component\Translation\TranslatorInterface;

final class InterviewContentHeaderConverter implements ViewModelConverter
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Interview $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\ContentHeaderNonArticle::basic(
            $object->getTitle(),
            false,
            $object->getSubTitle(),
            null,
            ViewModel\Meta::withText(
                $this->translator->trans('type.interview'),
                ViewModel\Date::simple($object->getPublishedDate())
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Interview && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
