<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Journal\Helper\LicenceUri;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InterviewContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Interview $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $meta = null;
        if ($date = $this->simpleDate($object, ['date' => 'published'] + $context)) {
            $meta = ViewModel\MetaNew::withDate($date);
        }

        return new ViewModel\ContentHeaderNew(
            $object->getTitle(),
            false, true, null, $object->getImpactStatement(), true,
            new ViewModel\Breadcrumb([
                new ViewModel\Link(
                    ModelName::singular('interview'),
                    $this->urlGenerator->generate('interviews')
                )
            ]),
            [], null, null, null, null, null,
            !empty($context['metrics']) ? ViewModel\ContextualData::withMetrics($context['metrics']) : null,
            null,
            $meta,
            null,
            LicenceUri::default()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Interview && ViewModel\ContentHeaderNew::class === $viewModel;
    }
}
