<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Event;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverEventHighlightItemConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesCoverPicture;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var Event $event */
        $event = $object->getItem();

        return new ViewModel\HighlightItem(
            [],
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('event', [$event])),
            ViewModel\Meta::withLink(
                new ViewModel\Link(
                    ModelName::singular('event'),
                    $this->urlGenerator->generate('events')
                ),
                $this->simpleDate($event, $context)
            ),
            $this->highlightItemCoverPicture($object),
            $object->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\HighlightItem::class === $viewModel && $object->getItem() instanceof Event;
    }
}
