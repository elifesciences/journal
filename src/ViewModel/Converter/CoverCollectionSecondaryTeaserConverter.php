<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Cover;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverCollectionSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;

    private $viewModelConverter;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var Collection $collection */
        $collection = $object->getItem();

        $curatedBy = 'Edited by '.$collection->getSelectedCurator()->getDetails()->getPreferredName();
        if ($collection->selectedCuratorEtAl()) {
            $curatedBy .= ' et al.';
        }

        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('collection', [$collection]),
            $curatedBy,
            $this->createContextLabel($collection),
            ViewModel\TeaserImage::small(
                $this->viewModelConverter->convert($object->getBanner(), null, ['width' => 72, 'height' => 72])
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
