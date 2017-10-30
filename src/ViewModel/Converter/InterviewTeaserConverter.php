<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class InterviewTeaserConverter implements ViewModelConverter
{
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
     * @param Interview $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return Teaser::main(
            $object->getTitle(),
            $this->urlGenerator->generate('interview', [$object]),
            $object->getImpactStatement(),
            null,
            null,
            $object->getThumbnail() ? $this->smallTeaserImage($object) : null,
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new Link('Interview', $this->urlGenerator->generate('interviews')),
                    $this->simpleDate($object, ['date' => 'published'] + $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Interview && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }
}
