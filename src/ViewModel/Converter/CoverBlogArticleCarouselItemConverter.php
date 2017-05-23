<?php

namespace eLife\Journal\ViewModel\Converter;

use Cocur\Slugify\SlugifyInterface;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverBlogArticleCarouselItemConverter implements ViewModelConverter
{
    use CreatesDate;

    private $urlGenerator;
    private $slugify;
    private $contentHeaderImageFactory;

    public function __construct(UrlGeneratorInterface $urlGenerator, SlugifyInterface $slugify, ContentHeaderImageFactory $contentHeaderImageFactory)
    {
        $this->urlGenerator = $urlGenerator;
        $this->slugify = $slugify;
        $this->contentHeaderImageFactory = $contentHeaderImageFactory;
    }

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var BlogArticle $article */
        $article = $object->getItem();

        return new ViewModel\CarouselItem(
            $article->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', ['id' => $subject->getId()]));
            })->toArray(),
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('inside-elife-article', ['id' => $article->getId(), 'slug' => $this->slugify->slugify($article->getTitle())])),
            'Read article',
            ViewModel\Meta::withLink(
                new ViewModel\Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                $this->simpleDate($article, $context)
            ),
            $this->contentHeaderImageFactory->forImage($object->getBanner())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\CarouselItem::class === $viewModel && $object->getItem() instanceof BlogArticle;
    }
}
