<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Event;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverEventSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;

    private $viewModelConverter;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var Event $event */
        $event = $object->getItem();

        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('event', [$event]),
            null,
            $this->createContextLabel($event),
            ViewModel\TeaserImage::small(
                $this->viewModelConverter->convert($object->getBanner(), null, ['width' => 72, 'height' => 72])
            ),
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular('event'),
                        $this->urlGenerator->generate('events')
                    ),
                    $this->simpleDate($event, $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null) && $object->getItem() instanceof Event;
    }
}
