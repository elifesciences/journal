<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PromotionalCollection;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class PromotionalCollectionContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    private $viewModelConverter;
    private $urlGenerator;
    private $contentHeaderImageFactory;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator, ContentHeaderImageFactory $contentHeaderImageFactory)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
        $this->contentHeaderImageFactory = $contentHeaderImageFactory;
    }

    /**
     * @param PromotionalCollection $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ContentHeader(
            $object->getTitle(),
            $this->contentHeaderImageFactory->forImage($object->getBanner(), true), $object->getImpactStatement(), true, null, [], null, [], [], null,
            new ViewModel\SocialMediaSharers(
                strip_tags($object->getTitle()),
                $this->urlGenerator->generate('promotional-collection', [$object], UrlGeneratorInterface::ABSOLUTE_URL)
            ),
            null,
            null,
            ViewModel\Meta::withText(
                'Highlights',
                $this->simpleDate($object, ['date' => 'published'] + $context)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PromotionalCollection && ViewModel\ContentHeader::class === $viewModel;
    }
}
