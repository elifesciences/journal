<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Interview;
use eLife\Journal\Helper\LicenceUri;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class InterviewContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Interview $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ContentHeader(
            $object->getTitle(),
            null,
            $object->getImpactStatement(),
            true,
            null,
            [],
            null,
            [],
            [],
            null,
            new ViewModel\SocialMediaSharers(
                strip_tags($object->getTitle()),
                $this->urlGenerator->generate('interview', [$object], UrlGeneratorInterface::ABSOLUTE_URL)
            ),
            null,
            null,
            ViewModel\Meta::withLink(
                new ViewModel\Link('Interview', $this->urlGenerator->generate('interviews')),
                $this->simpleDate($object, ['date' => 'published'] + $context)
            ),
            null,
            LicenceUri::default()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Interview && ViewModel\ContentHeader::class === $viewModel;
    }
}
