<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverInterviewHighlightItemConverter implements ViewModelConverter
{
    use CreatesDate;

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
        /** @var Interview $interview */
        $interview = $object->getItem();

        return new ViewModel\HighlightItem(
            [],
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('interview', [$interview])),
            ViewModel\Meta::withLink(new ViewModel\Link('Interview',
                $this->urlGenerator->generate('interviews')), $this->simpleDate($interview, $context)),
            (new PictureBuilderFactory())->forImage(
                $object->getBanner(), 339, 190
            )->build(),
            $object->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\HighlightItem::class === $viewModel && $object->getItem() instanceof Interview;
    }
}
