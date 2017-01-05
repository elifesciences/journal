<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Date;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class BlogArticleContentHeaderConverter implements ViewModelConverter
{
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
        return ContentHeaderNonArticle::basic($object->getTitle(), false, null, null,
            Meta::withLink(
                new Link(
                    $this->translator->trans('type.blog-article'),
                    $this->urlGenerator->generate('inside-elife')
                ),
                Date::simple($object->getPublishedDate())
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BlogArticle && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
