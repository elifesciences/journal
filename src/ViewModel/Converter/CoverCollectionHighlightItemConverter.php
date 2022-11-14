<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverCollectionHighlightItemConverter implements ViewModelConverter
{
    use CreatesDate;
    use CreatesCoverPicture;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var Collection $collection */
        $collection = $object->getItem();

        return new ViewModel\HighlightItem(
            $collection->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', [$subject]));
            })->toArray(),
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('collection', [$collection])),
            ViewModel\Meta::withLink(new ViewModel\Link(
                ModelName::singular('collection'),
                $this->urlGenerator->generate('collections')), $this->simpleDate($collection, $context)
            ),
            $this->highlightItemCoverPicture($object),
            $object->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\HighlightItem::class === $viewModel && $object->getItem() instanceof Collection;
    }
}
