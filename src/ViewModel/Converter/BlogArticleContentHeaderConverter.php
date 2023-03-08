<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Journal\Helper\LicenceUri;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strip_tags;

final class BlogArticleContentHeaderConverter implements ViewModelConverter
{
    use CreatesDate;

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
        if ($date = $this->simpleDate($object, ['date' => 'published'] + $context)) {
            $meta = ViewModel\MetaNew::withDate($date);
        } else {
            $meta = null;
        }

        return new ViewModel\ContentHeaderNew(
            $object->getTitle(),
            false,
            true, null, $object->getImpactStatement(), true,
            new ViewModel\Breadcrumb([
                new ViewModel\Link(
                    'Inside eLife',
                    $this->urlGenerator->generate('inside-elife')
                ),
            ]),
            [], null, null, null, null,
            new ViewModel\SocialMediaSharersNew(
                strip_tags($object->getTitle()),
                $this->urlGenerator->generate('inside-elife-article', [$object], UrlGeneratorInterface::ABSOLUTE_URL),
                false
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
        return $object instanceof BlogArticle && ViewModel\ContentHeaderNew::class === $viewModel;
    }
}
