<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\PressPackage;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PressPackageContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

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
        return ContentHeaderNonArticle::basic($object->getTitle(), false, null, null,
            Meta::withLink(
                new Link('Press pack', $this->urlGenerator->generate('press-packs')),
                $this->simpleDate($object, ['date' => 'published'] + $context)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof PressPackage && ViewModel\ContentHeaderNonArticle::class === $viewModel;
    }
}
