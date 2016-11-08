<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LabsExperimentSecondaryTeaserConverter implements ViewModelConverter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param LabsExperiment $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('labs-experiment', ['number' => $object->getNumber()]),
            null,
            null,
            ViewModel\TeaserImage::small(
                $object->getThumbnail()->getSize('1:1')->getImage(70),
                $object->getThumbnail()->getAltText(),
                [
                    140 => $object->getThumbnail()->getSize('1:1')->getImage(140),
                    70 => $object->getThumbnail()->getSize('1:1')->getImage(70),
                ]
            ),
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withText(
                    'Experiment: '.str_pad($object->getNumber(), 3, '0', STR_PAD_LEFT),
                    new ViewModel\Date($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof LabsExperiment && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
