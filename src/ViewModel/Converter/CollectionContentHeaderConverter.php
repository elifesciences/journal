<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CollectionContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesIiifUri;

    private $urlGenerator;
    private $contentHeaderImageFactory;

    public function __construct(UrlGeneratorInterface $urlGenerator, ContentHeaderImageFactory $contentHeaderImageFactory)
    {
        $this->urlGenerator = $urlGenerator;
        $this->contentHeaderImageFactory = $contentHeaderImageFactory;
    }

    /**
     * @param Collection $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $curatorName = $object->getSelectedCurator()->getDetails()->getPreferredName();
        if ($object->selectedCuratorEtAl()) {
            $curatorName .= ' et al.';
        }
        if ($object->getSelectedCurator()->getThumbnail()) {
            $curatorImage = new ViewModel\Picture(
                [],
                new ViewModel\Image(
                    $this->iiifUri($object->getSelectedCurator()->getThumbnail(), 70, 70),
                    $this->iiifUri($object->getSelectedCurator()->getThumbnail(), 140, 140)
                )
            );
        } else {
            $curatorImage = null;
        }

        return new ViewModel\ContentHeader(
            $object->getTitle(),
            $this->contentHeaderImageFactory->forImage($object->getBanner()), $object->getImpactStatement(), true, [], new ViewModel\Profile(new Link($curatorName), $curatorImage), [], [], null, null, null,
            ViewModel\Meta::withLink(
                new Link('Collection', $this->urlGenerator->generate('collections')),
                $this->simpleDate($object, ['date' => 'published'] + $context)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Collection && ViewModel\ContentHeader::class === $viewModel;
    }
}
