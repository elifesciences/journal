<?php

namespace eLife\Journal\ViewModel\Converter;

use Cocur\Slugify\SlugifyInterface;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Subject;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverCollectionCarouselItemConverter implements ViewModelConverter
{
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

        return new ViewModel\CarouselItem(
            $collection->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', ['id' => $subject->getId()]));
            })->toArray(),
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('collection', ['id' => $collection->getId(), 'slug' => $this->slugify->slugify($collection->getTitle())])),
            'Read collection',
            ViewModel\Meta::withLink(
                new ViewModel\Link('Collection', $this->urlGenerator->generate('collections')),
                ViewModel\Date::simple($collection->getPublishedDate())
            ),
            new ViewModel\BackgroundImage(
                $object->getBanner()->getSize('2:1')->getImage(900),
                $object->getBanner()->getSize('2:1')->getImage(1800)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && $object->getItem() instanceof Collection;
    }
}
