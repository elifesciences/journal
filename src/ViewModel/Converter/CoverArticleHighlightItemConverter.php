<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\ModelName;
use eLife\Journal\ViewModel\Factory\CarouselItemImageFactory;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverArticleHighlightItemConverter implements ViewModelConverter
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

        return new ViewModel\HighlightItem(
            $article->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', [$subject]));
            })->toArray(),
            new ViewModel\Link(
                $object->getTitle(),
                $this->urlGenerator->generate('article', [$article])
            ),
            ViewModel\Meta::withLink(
                new ViewModel\Link(
                    ModelName::singular($article->getType()),
                    $this->urlGenerator->generate('article-type', ['type' => $article->getType()])
                ),
                $this->simpleDate($article, $context)
            ),
            (new PictureBuilderFactory())->forImage(
                $object->getBanner(), $object->getBanner()->getWidth()
            )->build(),
            $article->getImpactStatement() ?? "",
            $article->getAuthorLine()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\HighlightItem::class === $viewModel && $object->getItem() instanceof ArticleVersion;
    }
}
