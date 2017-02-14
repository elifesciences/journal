<?php

namespace eLife\Journal\ViewModel\Converter;

use Cocur\Slugify\SlugifyInterface;
use eLife\ApiSdk\Model\Collection;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CollectionTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesTeaserImage;

    private $urlGenerator;
    private $slugify;

    public function __construct(UrlGeneratorInterface $urlGenerator, SlugifyInterface $slugify)
    {
        $this->urlGenerator = $urlGenerator;
        $this->slugify = $slugify;
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

        return Teaser::main(
            $object->getTitle(),
            $this->urlGenerator->generate('collection', ['id' => $object->getId(), 'slug' => $this->slugify->slugify($object->getTitle())]),
            $object->getImpactStatement(),
            $curatedBy,
            $this->createContextLabel($object),
            $this->bigTeaserImage($object),
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new Link('Collection', $this->urlGenerator->generate('collections')),
                    Date::simple($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Collection && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
