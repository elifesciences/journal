<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\ViewModel\Factory\CarouselItemImageFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverCollectionCarouselItemConverter implements ViewModelConverter
{
    use CreatesDate;

    private $urlGenerator;
    private $carouselItemImageFactory;

    public function __construct(UrlGeneratorInterface $urlGenerator, CarouselItemImageFactory $carouselItemImageFactory)
    {
        $this->urlGenerator = $urlGenerator;
        $this->carouselItemImageFactory = $carouselItemImageFactory;
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
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', [$subject]));
            })->toArray(),
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('collection', [$collection])),
            'Read collection',
            ViewModel\Meta::withLink(new ViewModel\Link('Collection', $this->urlGenerator->generate('collections')), $this->simpleDate($collection, $context)),
            $this->carouselItemImageFactory->forImage($object->getBanner())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\CarouselItem::class === $viewModel && $object->getItem() instanceof Collection;
    }
}
