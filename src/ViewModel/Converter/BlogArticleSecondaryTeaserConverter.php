<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class BlogArticleSecondaryTeaserConverter implements ViewModelConverter
{
    use CreatesContextLabel;

    private $urlGenerator;
    private $translator;

    public function __construct(UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    /**
     * @param BlogArticle $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return ViewModel\Teaser::secondary(
            $object->getTitle(),
            $this->urlGenerator->generate('inside-elife-article', ['id' => $object->getId()]),
            null,
            $this->createContextLabel($object),
            null,
            ViewModel\TeaserFooter::forNonArticle(
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        $this->translator->trans('type.blog-article'),
                        $this->urlGenerator->generate('inside-elife')
                    ),
                    ViewModel\Date::simple($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BlogArticle && ViewModel\Teaser::class === $viewModel && 'secondary' === ($context['variant'] ?? null);
    }
}
