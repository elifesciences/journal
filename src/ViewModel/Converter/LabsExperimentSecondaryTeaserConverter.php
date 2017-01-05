<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class LabsExperimentSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesTeaserImage;

    private $urlGenerator;
    private $translator;

    public function __construct(UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
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
            $this->smallTeaserImage($object),
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withText(
                    $this->translator->trans('type.labs-experiment', ['%number%' => str_pad($object->getNumber(), 3, '0', STR_PAD_LEFT)]),
                    ViewModel\Date::simple($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof LabsExperiment && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
