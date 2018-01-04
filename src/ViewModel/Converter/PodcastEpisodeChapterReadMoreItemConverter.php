<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Paragraph;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeChapterReadMoreItemConverter implements ViewModelConverter
{
    private $patternRenderer;
    private $urlGenerator;

    public function __construct(PatternRenderer $patternRenderer, UrlGeneratorInterface $urlGenerator)
    {
        $this->patternRenderer = $patternRenderer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param PodcastEpisodeChapterModel $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $chapter = $object->getChapter();

        return new ViewModel\ReadMoreItem(
            new ViewModel\ContentHeaderReadMore(
                $chapter->getLongTitle() ?? $chapter->getTitle(),
                $this->urlGenerator->generate('podcast-episode', [$object]),
                [],
                null,
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular('podcast-episode'),
                        $this->urlGenerator->generate('podcast')
                    )
                )
            ),
            $chapter->getImpactStatement() ? $this->patternRenderer->render(new Paragraph($chapter->getImpactStatement())) : null,
            $context['isRelated'] ?? false
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisodeChapterModel && ViewModel\ReadMoreItem::class === $viewModel;
    }
}
