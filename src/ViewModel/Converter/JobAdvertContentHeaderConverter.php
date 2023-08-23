<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\JobAdvert;
use eLife\Journal\Helper\LicenceUri;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class JobAdvertContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param JobAdvert $object
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
                    ModelName::singular('job-advert'),
                    $this->urlGenerator->generate('job-adverts')
                ),
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
        return $object instanceof JobAdvert && ViewModel\ContentHeaderNew::class === $viewModel;
    }
}
