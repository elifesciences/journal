<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Digest;
use eLife\Journal\Helper\LicenceUri;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class DigestContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Digest $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ContentHeaderNew(
            $object->getTitle(),
            null, $object->getImpactStatement(), true,
            new ViewModel\Breadcrumb([
                new ViewModel\Link(
                    'Magazine',
                    $this->urlGenerator->generate('magazine')
                ),
                new ViewModel\Link(
                    'Digest',
                    $this->urlGenerator->generate('digests')
                ),
            ]),
            [], null, null, null, null,
            new ViewModel\SocialMediaSharersNew(
                strip_tags($object->getTitle()),
                $this->urlGenerator->generate('digest', [$object], UrlGeneratorInterface::ABSOLUTE_URL)
            ),
            null, null,
            ViewModel\MetaNew::withDate(
                $this->simpleDate(
                    $object,
                    [
                        'date' => 'published',
                    ] + $context
                )
            ),
            null,
            LicenceUri::default()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Digest && ViewModel\ContentHeader::class === $viewModel;
    }
}
