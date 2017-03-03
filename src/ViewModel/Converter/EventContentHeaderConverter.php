<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Event;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class EventContentHeaderConverter implements ViewModelConverter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Event $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ContentHeaderNonArticle::basic(
            $object->getTitle(),
            false,
            null,
            null,
            ViewModel\Meta::withLink(new ViewModel\Link('Event', $this->urlGenerator->generate('events')), ViewModel\Date::simple($object->getStarts()))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Event && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
