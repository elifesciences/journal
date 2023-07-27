<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class SocialMediaSharersConverter implements ViewModelConverter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\SocialMediaSharersNew(
            strip_tags($object->getTitle()),
            $this->urlGenerator->generate($context['pageType'], [$object], UrlGeneratorInterface::ABSOLUTE_URL),
            false,
            true,
            true
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return ViewModel\SocialMediaSharersNew::class === $viewModel && in_array($context['pageType'], ['inside-elife-article', 'press-pack', 'digest']);
    }
}
