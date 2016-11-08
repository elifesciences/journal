<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use eLife\Patterns\ViewModel\TeaserImage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ArticleSecondaryTeaserConverter implements ViewModelConverter
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
            $image = TeaserImage::small(
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

        return Teaser::secondary(
            $object->getFullTitle(),
            $this->urlGenerator->generate('article', ['volume' => $object->getVolume(), 'id' => $object->getId()]),
            $object->getAuthorLine(),
            $this->createContextLabel($object),
            $image,
            TeaserFooter::forNonArticle(
                Meta::withText(
                    ucfirst(str_replace('-', ' ', $object->getType())),
                    new Date($object->getStatusDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
