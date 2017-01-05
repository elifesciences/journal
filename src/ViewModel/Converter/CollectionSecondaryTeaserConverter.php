<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class CollectionSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesTeaserImage;

    private $urlGenerator;
    private $translator;

    public function __construct(UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    /**
     * @param Collection $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $curatedBy = 'Curated by '.$object->getSelectedCurator()->getDetails()->getPreferredName();
        if ($object->selectedCuratorEtAl()) {
            $curatedBy .= ' et al';
        }
        $curatedBy .= '.';

        return Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('collection', ['id' => $object->getId()]),
            $curatedBy,
            $this->createContextLabel($object),
            $this->smallTeaserImage($object),
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new Link(
                        $this->translator->trans('type.collection'),
                        $this->urlGenerator->generate('collections')
                    ),
                    Date::simple($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Collection && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
