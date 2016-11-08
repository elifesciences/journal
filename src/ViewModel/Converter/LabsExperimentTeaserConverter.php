<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LabsExperimentTeaserConverter implements ViewModelConverter
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
        return Teaser::main(
            $object->getTitle(),
            $this->urlGenerator->generate('labs-experiment', ['number' => $object->getNumber()]),
            $object->getImpactStatement(),
            null,
            null,
            ViewModel\TeaserImage::big(
                $object->getThumbnail()->getSize('16:9')->getImage(250),
                $object->getThumbnail()->getAltText(),
                [
                    500 => $object->getThumbnail()->getSize('16:9')->getImage(500),
                    250 => $object->getThumbnail()->getSize('16:9')->getImage(250),
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
        return $object instanceof LabsExperiment && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
