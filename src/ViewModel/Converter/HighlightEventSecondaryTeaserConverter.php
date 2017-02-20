<?php

namespace eLife\Journal\ViewModel\Converter;

use Cocur\Slugify\SlugifyInterface;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\Highlight;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class HighlightEventSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesTeaserImage;

    private $urlGenerator;
    private $slugify;

    public function __construct(UrlGeneratorInterface $urlGenerator, SlugifyInterface $slugify)
    {
        $this->urlGenerator = $urlGenerator;
        $this->slugify = $slugify;
    }

    /**
     * @param Highlight $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var Event $event */
        $event = $object->getItem();

        return ViewModel\Teaser::event(
            $object->getTitle(),
            $this->urlGenerator->generate('event', ['id' => $event->getId(), 'slug' => $this->slugify->slugify($event->getTitle())]),
            $object->getAuthorLine(),
            ViewModel\Date::expanded($event->getStarts()),
            true,
            $object->getThumbnail() ? $this->smallTeaserImage($object) : null
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Highlight && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null) && $object->getItem() instanceof Event;
    }
}
