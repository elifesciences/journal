<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\JobAdvert;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Link;
use eLife\Patterns\ViewModel\Meta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class JobAdvertContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param JobAdvert $object
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
            null,
            null,
            new ViewModel\SocialMediaSharers(
                strip_tags($object->getTitle()),
                $this->urlGenerator->generate('job-advert', [$object], UrlGeneratorInterface::ABSOLUTE_URL)
            ),
            null,
            Meta::withLink(
                new Link('eLife jobs', $this->urlGenerator->generate('job-adverts')),
                $this->simpleDate($object, ['date' => 'published'] + $context)
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof JobAdvert && ViewModel\ContentHeader::class === $viewModel;
    }
}
