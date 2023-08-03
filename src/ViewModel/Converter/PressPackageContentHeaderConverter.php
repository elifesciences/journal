<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PressPackage;
use eLife\Journal\Helper\LicenceUri;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PressPackageContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param PressPackage $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($date = $this->simpleDate($object, ['date' => 'published'] + $context)) {
            $meta = ViewModel\MetaNew::withDate($date);
        } else {
            $meta = null;
        }

        return new ViewModel\ContentHeaderNew(
            $object->getTitle(),
            false, true, null, $object->getImpactStatement(), true,
            new ViewModel\Breadcrumb([
                new ViewModel\Link(
                    ModelName::singular('press-package'),
                    $this->urlGenerator->generate('press-packs')
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
        return $object instanceof PressPackage && ViewModel\ContentHeaderNew::class === $viewModel;
    }
}
