<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;

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
            $image = ViewModel\TeaserImage::small(
                $object->getThumbnail()->getSize('1:1')->getImage(70),
                $object->getThumbnail()->getAltText(),
                [
                    140 => $object->getThumbnail()->getSize('1:1')->getImage(140),
                    70 => $object->getThumbnail()->getSize('1:1')->getImage(70),
                ]
            );
        } else {
            $image = null;
        }

        return ViewModel\Teaser::main(
            $object->getFullTitle(),
            $this->urlGenerator->generate('article', ['volume' => $object->getVolume(), 'id' => $object->getId()]),
            $object instanceof ArticleVoR ? $object->getImpactStatement() : null,
            $object->getAuthorLine(),
            $this->createContextLabel($object),
            $image,
            ViewModel\TeaserFooter::forArticle(
                ViewModel\Meta::withText(
                    ucfirst(str_replace('-', ' ', $object->getType())),
                    new ViewModel\Date($object->getStatusDate())
                ),
                true
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
