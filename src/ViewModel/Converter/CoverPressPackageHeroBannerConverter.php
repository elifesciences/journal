<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\PressPackage;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverPressPackageHeroBannerConverter implements ViewModelConverter
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
        /** @var PressPackage $pressPackage */
        $pressPackage = $object->getItem();

        return new ViewModel\HeroBanner(
            $pressPackage->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', [$subject]));
            })->toArray(),
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('press-pack', [$pressPackage])),
            ViewModel\Meta::withLink(
                new ViewModel\Link(
                    ModelName::singular('press-package'),
                    $this->urlGenerator->generate('press-packs')
                ),
                $this->simpleDate($pressPackage, $context)
            ),
            $this->heroBannerCoverPicture($object),
            $object->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\HeroBanner::class === $viewModel && $object->getItem() instanceof PressPackage;
    }
}
