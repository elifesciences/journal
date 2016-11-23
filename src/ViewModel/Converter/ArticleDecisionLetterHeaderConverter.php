<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVoR;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class ArticleDecisionLetterHeaderConverter implements ViewModelConverter
{
    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param ArticleVoR $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\DecisionLetterHeader(
            $this->patternRenderer->render(
                ...$object->getDecisionLetterDescription()
                ->map([$this->viewModelConverter, 'convert'])
            ),
            $object->getReviewers()
                ->map([$this->viewModelConverter, 'convert'])
                ->toArray()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVoR && ViewModel\DecisionLetterHeader::class === $viewModel && $object->getDecisionLetterDescription()->notEmpty();
    }
}
