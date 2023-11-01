<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ExternalArticle;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\Journal\Helper\ModelName;
use eLife\Journal\Helper\ModelRelationship;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ReviewedPreprintRelatedItemTeaserConverter implements ViewModelConverter
{
    use CreatesDate;

    private $viewModelConverter;
    private $urlGenerator;

    public function __construct(ViewModelConverter $viewModelConverter, UrlGeneratorInterface $urlGenerator)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param ReviewedPreprint $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return Teaser::relatedItem(
            $object->getTitle(),
            $this->urlGenerator->generate('reviewed-preprint', ['id' => $object->getId()]),
            $object->getAuthorLine(),
            new ViewModel\ContextLabel(new ViewModel\Link(ModelRelationship::get($context['from'], 'reviewed-preprint', $context['related'] ?? false))),
            null,
            TeaserFooter::forNonArticle(
                Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular('reviewed-preprint'),
                        $this->urlGenerator->generate('reviewed-preprints')
                    ),
                    $this->simpleDate($object, $context)
                )
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ReviewedPreprint && !empty($context['from']) && ViewModel\Teaser::class === $viewModel && 'relatedItem' === ($context['variant'] ?? null);
    }
}
