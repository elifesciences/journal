<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesTeaserImage;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        if ($object instanceof ArticleVoR && $object->getThumbnail()) {
            $image = $this->smallTeaserImage($object);
        } else {
            $image = null;
        }

        return ViewModel\Teaser::main(
            $object->getFullTitle(),
            $this->urlGenerator->generate('article', [$object]),
            $object instanceof ArticleVoR ? $object->getImpactStatement() : null,
            $object->getAuthorLine(),
            $this->createContextLabel($object),
            $image,
            ViewModel\TeaserFooter::forArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular($object->getType()),
                        $this->urlGenerator->generate('article-type', ['type' => $object->getType()])
                    ),
                    $this->simpleDate($object, $context)
                ),
                $object instanceof ArticleVoR || null === $object->getPdf(),
                null !== $object->getPdf()
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
