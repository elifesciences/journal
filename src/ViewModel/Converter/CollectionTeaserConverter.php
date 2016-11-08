<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use eLife\Patterns\ViewModel\TeaserImage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CollectionTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
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
            $this->urlGenerator->generate('collection', ['id' => $object->getId()]),
            $object->getImpactStatement(),
            $curatedBy,
            $this->createContextLabel($object),
            TeaserImage::big(
                $object->getThumbnail()->getSize('16:9')->getImage(250),
                $object->getThumbnail()->getAltText(),
                [
                    500 => $object->getThumbnail()->getSize('16:9')->getImage(500),
                    250 => $object->getThumbnail()->getSize('16:9')->getImage(250),
                ]
            ),
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new Link('Collection', $this->urlGenerator->generate('collections')),
                    new Date($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Collection && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
