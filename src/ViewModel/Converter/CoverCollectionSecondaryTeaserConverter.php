<?php

namespace eLife\Journal\ViewModel\Converter;

use Cocur\Slugify\SlugifyInterface;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Cover;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverCollectionSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesIiifUri;

    private $urlGenerator;
    private $slugify;

    public function __construct(UrlGeneratorInterface $urlGenerator, SlugifyInterface $slugify)
    {
        $this->urlGenerator = $urlGenerator;
        $this->slugify = $slugify;
    }

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var Collection $collection */
        $collection = $object->getItem();

        $curatedBy = 'Curated by '.$collection->getSelectedCurator()->getDetails()->getPreferredName();
        if ($collection->selectedCuratorEtAl()) {
            $curatedBy .= ' et al.';
        }

        $url = $this->urlGenerator->generate('collection', ['id' => $collection->getId(), 'slug' => $this->slugify->slugify($collection->getTitle())]);

        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $url,
            $curatedBy,
            $this->createContextLabel($collection),
            ViewModel\TeaserImage::small(
                $this->iiifUri($object->getBanner(), 70, 70),
                $object->getBanner()->getAltText(),
                [
                    140 => $this->iiifUri($object->getBanner(), 140, 140),
                    70 => $this->iiifUri($object->getBanner(), 70, 70),
                ]
            ),
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link('Collection', $this->urlGenerator->generate('collections')),
                    $this->simpleDate($collection, $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null) && $object->getItem() instanceof Collection;
    }
}
