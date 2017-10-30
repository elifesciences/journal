<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class HighlightPodcastEpisodeChapterSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesTeaserImage;

    private $viewModelConverter;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Highlight $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var PodcastEpisodeChapterModel $model */
        $model = $object->getItem();

        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('podcast-episode', [$model]),
            null,
            $this->createContextLabel($model->getEpisode()),
            $object->getThumbnail() ? $this->smallTeaserImage($object) : null,
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link('Podcast', $this->urlGenerator->generate('podcast')),
                    $this->simpleDate($model->getEpisode(), $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Highlight && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null) && $object->getItem() instanceof PodcastEpisodeChapterModel;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
