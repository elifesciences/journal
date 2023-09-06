<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Event;
use eLife\Journal\Helper\LicenceUri;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
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
        $meta = ViewModel\MetaNew::withDate(ViewModel\Date::simple($object->getStarts()));

        return new ViewModel\ContentHeaderNew(
            $object->getTitle(),
            false, true, null, $object->getImpactStatement(), true,
            new ViewModel\Breadcrumb([
                new ViewModel\Link(
                    ModelName::singular('event'),
                    $this->urlGenerator->generate('events')
                ),
            ]),
            [],
            null, null, null, null, null,
            !empty($context['metrics']) ? ViewModel\ContextualData::withMetrics($context['metrics']) : null,
            null,
            $meta,
            null,
            LicenceUri::default()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Event && ViewModel\ContentHeaderNew::class === $viewModel;
    }
}
