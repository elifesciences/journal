<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PodcastEpisodeChapterModel;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\ModelName;
use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PodcastEpisodeChapterReadMoreItemConverter implements ViewModelConverter
{
    use CreatesDate;

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
        $episode = $object->getEpisode();

        return new ViewModel\ReadMoreItem(
            new ViewModel\ContentHeaderReadMore(
                sprintf('Episode %s: %s. %s', $episode->getNumber(), $chapter->getNumber(), $chapter->getTitle()),
                $this->urlGenerator->generate('podcast-episode', ['number' => $episode->getNumber()]).'#'.$chapter->getTime(),
                $episode->getSubjects()->map(function (Subject $subject) {
                    return new ViewModel\Link($subject->getName());
                })->toArray(),
                null,
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular('podcast-episode'),
                        $this->urlGenerator->generate('podcast')
                    ),
                    $this->simpleDate($episode, $context)
                )
            ),
            $chapter->getImpactStatement() ? $this->patternRenderer->render(new Paragraph($chapter->getImpactStatement())) : null
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PodcastEpisodeChapterModel && ViewModel\ReadMoreItem::class === $viewModel;
    }
}
