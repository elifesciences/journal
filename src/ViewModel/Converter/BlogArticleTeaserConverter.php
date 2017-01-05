<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class BlogArticleTeaserConverter implements ViewModelConverter
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
        return Teaser::main(
            $object->getTitle(),
            $this->urlGenerator->generate('inside-elife-article', ['id' => $object->getId()]),
            $object->getImpactStatement(),
            null,
            $this->createContextLabel($object),
            null,
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new ViewModel\Link(
                        $this->translator->trans('type.blog-article'),
                        $this->urlGenerator->generate('inside-elife')
                    ),
                    Date::simple($object->getPublishedDate())
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BlogArticle && ViewModel\Teaser::class === $viewModel && empty($context['variant']);
    }
}
