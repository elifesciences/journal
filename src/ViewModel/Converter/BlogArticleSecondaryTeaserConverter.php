<?php

namespace eLife\Journal\ViewModel\Converter;

use Cocur\Slugify\SlugifyInterface;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlogArticleSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;

    private $urlGenerator;
    private $slugify;

    public function __construct(UrlGeneratorInterface $urlGenerator, SlugifyInterface $slugify)
    {
        $this->urlGenerator = $urlGenerator;
        $this->slugify = $slugify;
    }

    /**
     * @param BlogArticle $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('inside-elife-article', ['id' => $object->getId(), 'slug' => $this->slugify->slugify($object->getTitle())]),
            null,
            $this->createContextLabel($object),
            null,
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                    ViewModel\Date::simple($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BlogArticle && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
