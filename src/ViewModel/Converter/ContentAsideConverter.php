<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Button;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ContentAsideConverter implements ViewModelConverter
{
    use CanConvertContent;
    use CreatesDate;

    private $viewModelConverter;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $status = $this->getStatus($object);
        return new ViewModel\ContentAside(
            $status,
            new ViewModel\ButtonCollection([
                Button::action('Download', '#downloads', true, null, Button::ACTION_VARIANT_DOWNLOAD),
                Button::action('Cite', '#citations', true, 'modalContentCitations', Button::ACTION_VARIANT_CITATION),
                Button::action('Share', '#share', true, 'modalContentShare', Button::ACTION_VARIANT_SHARE),
                Button::action('Comment<span aria-hidden=\'true\'><span data-visible-annotation-count></span> </span><span class=\'visuallyhidden\'>Open annotations (there are currently <span data-hypothesis-annotation-count>0</span> annotations on this page). </span>', '#comment', true, null, Button::ACTION_VARIANT_COMMENT),
            ], true),
            !empty($context['metrics']) ? ViewModel\ContextualData::withMetrics($context['metrics']) : null,
            (!empty($context['timeline']) && $status !== null) ? ViewModel\DefinitionList::timeline($context['timeline']) : null,
            $context['relatedItem'] ?? null
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion &&
            !in_array($object->getType(), ['insight', 'editorial', 'feature']) &&
            ViewModel\ContentAside::class === $viewModel;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }

    private function getStatus(ArticleVersion $article) : ?ViewModel\ContentAsideStatus
    {
        if (in_array($article->getType(), [
            'correction',
            'retraction',
            'registered-report',
            'replication-study',
            'research-communication',
        ])) {
             return null;
        }

        $title = ($article instanceof ArticleVoR) ? 'Version of Record' : 'Author Accepted Manuscript';

        switch (true) {
            case $article instanceof ArticlePoA:
                $text = 'PDF only version. The full online version will follow soon.';
                break;
            case $article instanceof ArticleVoR && $article->isReviewedPreprint():
                $text = 'The authors declare this version of their article to be the Version of Record';
                break;
            default:
                $text = 'Accepted for publication after peer review and revision.';
        }

        return new ViewModel\ContentAsideStatus(
            $title,
            $text,
            new ViewModel\Link(
                'About eLife\'s process',
                $this->urlGenerator->generate('inside-elife-article', ['id' => '54d63486'])
            )
        );
    }
}
