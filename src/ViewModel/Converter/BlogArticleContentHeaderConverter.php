<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Journal\Helper\LicenceUri;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Link;
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
        return new ViewModel\ContentHeader(
            $object->getTitle(), null, $object->getImpactStatement(), true, [],
            null, null, null,
            new ViewModel\SocialMediaSharers(
                strip_tags($object->getTitle()),
                $this->urlGenerator->generate('inside-elife-article', [$object], UrlGeneratorInterface::ABSOLUTE_URL)
            ),
            null,
            ViewModel\Meta::withLink(
                new Link('Inside eLife', $this->urlGenerator->generate('inside-elife')),
                $this->simpleDate($object, ['date' => 'published'] + $context)
            ), LicenceUri::default()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof BlogArticle && ViewModel\ContentHeader::class === $viewModel;
    }
}
