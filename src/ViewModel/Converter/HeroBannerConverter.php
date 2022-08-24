<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class HeroBannerConverter implements ViewModelConverter
{
    use CreatesDate;

    private $urlGenerator;
    private $carouselItemImageFactory;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var ArticleVersion $article */
        $article = $object->getItem();

        return new ViewModel\HeroBanner(
            $article->getImpactStatement(),
            $article->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', [$subject]));
            })->toArray(),
            new ViewModel\Link(
                $article->getTitle(),
                $this->urlGenerator->generate('article', [$article])
            ),
            $article->getAuthorLine(),
            Meta::withText(
                ModelName::singular($article->getType()),
                ViewModel\Date::simple($article->getPublishedDate())
            ),
            (new PictureBuilderFactory())->forImage(
                $object->getBanner(), $object->getBanner()->getWidth()
            )->build()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && $viewModel instanceof ViewModel\HeroBanner;
    }
}
