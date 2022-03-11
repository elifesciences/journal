<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Digest;
use eLife\Journal\Helper\LicenceUri;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Link;
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
        return new ViewModel\ContentHeader(
            $object->getTitle(),
            null, $object->getImpactStatement(), true, null, [], null, [], [], null,
            new ViewModel\SocialMediaSharers(
                strip_tags($object->getTitle()),
                $this->urlGenerator->generate('digest', [$object], UrlGeneratorInterface::ABSOLUTE_URL)
            ),
            null,
            null,
            ViewModel\Meta::withLink(
                new Link('Digest', $this->urlGenerator->generate('digests')),
                $this->simpleDate($object, ['date' => 'published'] + $context)
            ), null, LicenceUri::default()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Digest && ViewModel\ContentHeader::class === $viewModel;
    }
}
