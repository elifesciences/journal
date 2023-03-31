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
        if ($date = $this->simpleDate($object, ['date' => 'published'] + $context)) {
            $meta = ViewModel\MetaNew::withDate($date);
        } else {
            $meta = null;
        }

        return new ViewModel\ContentHeaderNew(
            $object->getTitle(),
            false, true, null, $object->getImpactStatement(), true,
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
                $this->urlGenerator->generate('digest', [$object], UrlGeneratorInterface::ABSOLUTE_URL),
                false,
                true
            ),
            !empty($context['metrics']) ? ViewModel\ContextualData::withMetrics($context['metrics']) : null,
            null,
            $meta,
            null,
            LicenceUri::default()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Digest && ViewModel\ContentHeaderNew::class === $viewModel;
    }
}
