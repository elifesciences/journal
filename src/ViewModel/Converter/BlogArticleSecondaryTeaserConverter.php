<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlogArticleSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param BlogArticle $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('inside-elife-article', [$object]),
            null,
            $this->createContextLabel($object),
            null,
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                    $this->simpleDate($object, $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BlogArticle && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
