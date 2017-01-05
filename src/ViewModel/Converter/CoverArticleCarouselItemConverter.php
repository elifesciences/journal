<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\ArticleType;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverArticleCarouselItemConverter implements ViewModelConverter
{
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
        /** @var ArticleVersion $article */
        $article = $object->getItem();

        return new ViewModel\CarouselItem(
            $article->getSubjects()->map(function (Subject $subject) {
                return new ViewModel\Link($subject->getName(), $this->urlGenerator->generate('subject', ['id' => $subject->getId()]));
            })->toArray(),
            new ViewModel\Link(
                $object->getTitle(),
                $this->urlGenerator->generate('article', ['volume' => $article->getVolume(), 'id' => $article->getId()])
            ),
            'Read article',
            ViewModel\Meta::withText(
                ArticleType::singular($article->getType()),
                $article->getStatusDate() ? ViewModel\Date::simple($article->getStatusDate(), $article->getStatusDate() != $article->getPublishedDate()) : null
            ),
            new ViewModel\BackgroundImage(
                $object->getBanner()->getSize('2:1')->getImage(900),
                $object->getBanner()->getSize('2:1')->getImage(1800)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && $object->getItem() instanceof ArticleVersion;
    }
}
