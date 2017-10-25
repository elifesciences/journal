<?php

namespace eLife\Journal\ViewModel\Converter;

use \eLife\ApiSdk\Model\JobAdvert;
use eLife\Journal\Helper\LicenceUri;
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
        return new ViewModel\ContentHeader(
            $object->getTitle(),
            null,
            $object->getImpactStatement(),
            true,
            [],
            null,
            [],
            [],
            null,
            null,
            null,
            null,
            LicenceUri::default()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof JobAdvert && ViewModel\ContentHeader::class === $viewModel;
    }
}
