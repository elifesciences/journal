<?php

namespace eLife\Journal\ViewModel\Converter;

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
    private $contentHeaderImageFactory;

    public function __construct(UrlGeneratorInterface $urlGenerator, ContentHeaderImageFactory $contentHeaderImageFactory)
    {
        $this->urlGenerator = $urlGenerator;
        $this->contentHeaderImageFactory = $contentHeaderImageFactory;
    }

    /**
     * @param Cover $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        /** @var BlogArticle $blogArticle */
        $blogArticle = $object->getItem();

        return new ViewModel\CarouselItem(
            $blogArticle->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', [$subject]));
            })->toArray(),
            new ViewModel\Link($object->getTitle(), $this->urlGenerator->generate('inside-elife-article', [$blogArticle])),
            'Read article',
            ViewModel\Meta::withLink(
                new ViewModel\Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                $this->simpleDate($blogArticle, $context)
            ),
            $this->contentHeaderImageFactory->forImage($object->getBanner())
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\CarouselItem::class === $viewModel && $object->getItem() instanceof BlogArticle;
    }
}
