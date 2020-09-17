<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PressPackage;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PressPackageTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
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
        return Teaser::main(
            $object->getTitle(),
            $this->urlGenerator->generate('press-pack', [$object]),
            $object->getImpactStatement(),
            null,
            $this->createContextLabel($object),
            null,
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new Link('Press Pack', $this->urlGenerator->generate('press-packs')),
                    $this->simpleDate($object, $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PressPackage && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
