<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CollectionContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesIiifUri;

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
        $curatorName = $object->getSelectedCurator()->getDetails()->getPreferredName();
        if ($object->selectedCuratorEtAl()) {
            $curatorName .= ' et al';
        }
        if ($object->getSelectedCurator()->getThumbnail()) {
            $curatorImage = new ViewModel\Picture(
                [],
                new ViewModel\Image(
                    $this->iiifUri($object->getSelectedCurator()->getThumbnail(), 40, 40),
                    [
                        80 => $this->iiifUri($object->getSelectedCurator()->getThumbnail(), 80, 80),
                    ]
                )
            );
        } else {
            $curatorImage = null;
        }

        return ContentHeaderNonArticle::curatedContentListing($object->getTitle(), false,
            $object->getSubTitle(),
            null,
            Meta::withLink(
                new Link('Collection', $this->urlGenerator->generate('collections')),
                $this->simpleDate($object, ['date' => 'published'] + $context)
            ),
            new ViewModel\Profile(new Link($curatorName), $curatorImage),
            new ViewModel\BackgroundImage(
                $this->iiifUri($object->getBanner(), 900, 450),
                $this->iiifUri($object->getBanner(), 1800, 900)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Collection && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
