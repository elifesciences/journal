<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Annotation;
use eLife\Journal\Helper\CanConvert;
use eLife\Journal\Helper\Html;
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

        $content = $this->patternRenderer->render(...$object->getContent()->map($this->willConvertTo()));
        $content = Html::stripElement($content, 'a');

        if ($object->getParents()->notEmpty()) {
            return ViewModel\AnnotationTeaser::forReply(
                $object->getDocument()->getTitle(),
                $date,
                $object->getDocument()->getUri(),
                $content,
                $isRestricted
            );
        }

        if ($object->getHighlight() && !$content) {
            return ViewModel\AnnotationTeaser::forHighlight(
                $object->getDocument()->getTitle(),
                $date,
                $object->getDocument()->getUri(),
                $object->getHighlight(),
                $isRestricted
            );
        }

        if (!$object->getHighlight() && $content) {
            return ViewModel\AnnotationTeaser::forPageNote(
                $object->getDocument()->getTitle(),
                $date,
                $object->getDocument()->getUri(),
                $content,
                $isRestricted
            );
        }

        return ViewModel\AnnotationTeaser::forAnnotation(
            $object->getDocument()->getTitle(),
            $date,
            $object->getDocument()->getUri(),
            $object->getHighlight(),
            $content,
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
