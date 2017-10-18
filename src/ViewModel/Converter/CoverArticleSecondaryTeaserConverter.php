<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Cover;
use eLife\Journal\Helper\CreatesIiifUri;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CoverArticleSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesDate;
    use CreatesIiifUri;

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

        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('article', [$article]),
            $article->getAuthorLine(),
            $this->createContextLabel($article),
            ViewModel\TeaserImage::small(
                new ViewModel\Picture(
                    [[
                        'srcset' => implode(', ', array_map(function (int $width, string $uri) {
                            return "{$uri} {$width}w";
                        }, [140, 70], [$this->iiifUri($object->getBanner(), 140, 140, 'webp'), $this->iiifUri($object->getBanner(), 70, 70, 'webp')])),
                        'type' => 'image/webp',
                    ]],
                    new ViewModel\Image(
                        $this->iiifUri($object->getBanner(), 70, 70),
                        [
                            140 => $this->iiifUri($object->getBanner(), 140, 140),
                            70 => $this->iiifUri($object->getBanner(), 70, 70),
                        ],
                        $object->getBanner()->getAltText()
                    )
                )
            ),
            ViewModel\TeaserFooter::forNonArticle(
                Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular($article->getType()),
                        $this->urlGenerator->generate('article-type', ['type' => $article->getType()])
                    ),
                    $this->simpleDate($article, $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Cover && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null) && $object->getItem() instanceof ArticleVersion;
    }
}
