<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Annotation;
use eLife\Journal\Helper\CanConvert;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class AnnotationAnnotationTeaserConverter implements ViewModelConverter
{
    use CanConvert;

    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Annotation $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $date = ViewModel\Date::simple($object->getUpdatedDate() ?? $object->getCreatedDate());
        $isRestricted = 'public' !== $object->getAccess();

        $content = $object->getContent()->map($this->willConvertTo());

        if ($object->getParents()->notEmpty()) {
            return ViewModel\AnnotationTeaser::reply(
                $object->getDocument()->getTitle(),
                $date,
                $object->getDocument()->getUri(),
                $this->patternRenderer->render(...$content),
                $isRestricted
            );
        }

        if ($object->getHighlight() && $content->isEmpty()) {
            return ViewModel\AnnotationTeaser::highlight(
                $object->getDocument()->getTitle(),
                $date,
                $object->getDocument()->getUri(),
                $object->getHighlight(),
                $isRestricted
            );
        }

        if (!$object->getHighlight() && $content->notEmpty()) {
            return ViewModel\AnnotationTeaser::pageNote(
                $object->getDocument()->getTitle(),
                $date,
                $object->getDocument()->getUri(),
                $this->patternRenderer->render(...$content),
                $isRestricted
            );
        }

        return ViewModel\AnnotationTeaser::full(
            $object->getDocument()->getTitle(),
            $date,
            $object->getDocument()->getUri(),
            $object->getHighlight(),
            $this->patternRenderer->render(...$content),
            $isRestricted
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Annotation && ViewModel\AnnotationTeaser::class === $viewModel;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
