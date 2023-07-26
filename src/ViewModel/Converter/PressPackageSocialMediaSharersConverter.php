<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PressPackage;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class PressPackageSocialMediaSharersConverter implements ViewModelConverter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param PressPackage $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\SocialMediaSharersNew(
            strip_tags($object->getTitle()),
            $this->urlGenerator->generate('press-pack', [$object], UrlGeneratorInterface::ABSOLUTE_URL),
            false,
            true,
            true
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PressPackage && ViewModel\SocialMediaSharersNew::class === $viewModel;
    }
}
