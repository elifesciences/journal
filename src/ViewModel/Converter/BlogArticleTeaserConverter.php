<?php

namespace eLife\Journal\ViewModel\Converter;

use Cocur\Slugify\SlugifyInterface;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlogArticleTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;

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
        return Teaser::main(
            $object->getTitle(),
            $this->urlGenerator->generate('inside-elife-article', ['id' => $object->getId(), 'slug' => $this->slugify->slugify($object->getTitle())]),
            $object->getImpactStatement(),
            null,
            $this->createContextLabel($object),
            null,
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                    $this->simpleDate($object, $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BlogArticle && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
