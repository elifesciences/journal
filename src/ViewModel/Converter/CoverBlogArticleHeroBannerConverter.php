<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverBlogArticleHeroBannerConverter implements ViewModelConverter
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
        /** @var BlogArticle $blogArticle */
        $blogArticle = $object->getItem();

        return new ViewModel\HeroBanner(
            $blogArticle->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', [$subject]));
            })->toArray(),
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('inside-elife-article', [$blogArticle])),
            ViewModel\Meta::withLink(
                new ViewModel\Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                $this->simpleDate($blogArticle, $context)
            ),
            (new PictureBuilderFactory())->forImage(
                $object->getBanner(), $object->getBanner()->getWidth()
            )->build(),
            $blogArticle->getImpactStatement()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\HeroBanner::class === $viewModel && $object->getItem() instanceof BlogArticle;
    }
}
