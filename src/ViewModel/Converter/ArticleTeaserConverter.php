<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class ArticleTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;
    use CreatesTeaserImage;

    private $urlGenerator;
    private $translator;

    public function __construct(UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
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
            $this->urlGenerator->generate('article', ['volume' => $object->getVolume(), 'id' => $object->getId()]),
            $object instanceof ArticleVoR ? $object->getImpactStatement() : null,
            $object->getAuthorLine(),
            $this->createContextLabel($object),
            $image,
            ViewModel\TeaserFooter::forArticle(
                ViewModel\Meta::withText(
                    $this->translator->trans('type.'.$object->getType()),
                    $object->getStatusDate() ? ViewModel\Date::simple($object->getStatusDate(), $object->getStatusDate() != $object->getPublishedDate()) : null
                ),
                $object instanceof ArticleVoR
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ArticleVersion && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
