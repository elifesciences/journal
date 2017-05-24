<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use eLife\Journal\Helper\ModelName;
use eLife\Journal\Helper\ModelRelationship;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeChapterRelatedItemTeaserConverter implements ViewModelConverter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param PodcastEpisodeChapterModel $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $chapter = $object->getChapter();
        $episode = $object->getEpisode();

        return ViewModel\Teaser::relatedItem(
            $chapter->getLongTitle() ?? $chapter->getTitle(),
            $this->urlGenerator->generate('podcast-episode', ['number' => $episode->getNumber()]).'#'.$chapter->getTime(),
            null,
            new ViewModel\ContextLabel(new ViewModel\Link(ModelRelationship::get($context['from'], 'podcast-episode-chapter'))),
            null,
            TeaserFooter::forNonArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular('podcast-episode'),
                        $this->urlGenerator->generate('podcast')
                    )
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisodeChapterModel && !empty($context['from']) && ViewModel\Teaser::class === $viewModel && 'relatedItem' === ($context['variant'] ?? null);
    }
}
