<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Patterns\ViewModel;
use Symfony\Component\Translation\TranslatorInterface;

final class LabsExperimentContentHeaderConverter implements ViewModelConverter
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param LabsExperiment $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\ContentHeaderNonArticle::basic($object->getTitle(), false, null, null,
            ViewModel\Meta::withText(
                $this->translator->trans('type.labs-experiment', ['%number%' => str_pad($object->getNumber(), 3, '0', STR_PAD_LEFT)]),
                ViewModel\Date::simple($object->getPublishedDate())
            ),
            new ViewModel\BackgroundImage(
                $object->getBanner()->getSize('2:1')->getImage(900),
                $object->getBanner()->getSize('2:1')->getImage(1800)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof LabsExperiment && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
