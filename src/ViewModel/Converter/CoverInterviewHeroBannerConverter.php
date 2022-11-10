<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Interview;
use eLife\Journal\Helper\ModelName;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverInterviewHeroBannerConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesHeroBannerPicture;

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
        /** @var Interview $interview */
        $interview = $object->getItem();

        return new ViewModel\HeroBanner(
            [],
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('interview', [$interview])),
            ViewModel\Meta::withLink(
                new ViewModel\Link(
                    ModelName::singular('interview'),
                    $this->urlGenerator->generate('interviews')
                ),
                $this->simpleDate($interview, $context)
            ),
            $this->createHeroBannerPicture($object),
            $object->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\HeroBanner::class === $viewModel && $object->getItem() instanceof Interview;
    }
}
