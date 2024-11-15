<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ReviewedPreprintTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesTeaserImage;

    private $viewModelConverter;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param ReviewedPreprint $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        $meta = $object->getVersion()
            ? ViewModel\Meta::withLink(
                new ViewModel\Link(
                    ModelName::singular('reviewed-preprint') . ' v' . $object->getVersion()
                ),
                $this->simpleDate($object, $context),
                $object->getVersion() === 1 ? ViewModel\Meta::STATUS_NOT_REVISED : ViewModel\Meta::STATUS_REVISED,
                $object->getVersion() === 1 ? ViewModel\Meta::COLOR_NOT_REVISED : ViewModel\Meta::COLOR_REVISED
            )
            : ViewModel\Meta::withLink(
                new ViewModel\Link(
                    ModelName::singular('reviewed-preprint')
                ),
                $this->simpleDate($object, $context)
            );

        return ViewModel\Teaser::main(
            $object->getTitle(),
            $this->urlGenerator->generate('reviewed-preprint', ['id' => $object->getId()]),
            null,
            $object->getAuthorLine(),
            $this->createContextLabel($object),
            $object->getThumbnail() ? $this->smallTeaserImage($object) : null,
            ViewModel\TeaserFooter::forArticle(
                $meta
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ReviewedPreprint && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
