<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Journal\Helper\LicenceUri;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class BlogArticleSocialMediaSharersConverter implements ViewModelConverter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param BlogArticle $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\SocialMediaSharersNew(
            strip_tags($object->getTitle()),
            $this->urlGenerator->generate('inside-elife-article', [$object], UrlGeneratorInterface::ABSOLUTE_URL),
            false,
            true,
            true
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BlogArticle && ViewModel\SocialMediaSharersNew::class === $viewModel;
    }
}
