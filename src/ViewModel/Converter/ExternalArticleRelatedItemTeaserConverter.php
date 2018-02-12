<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ExternalArticle;
use eLife\Journal\Helper\ModelRelationship;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\Meta;
use eLife\Patterns\ViewModel\Teaser;
use eLife\Patterns\ViewModel\TeaserFooter;

final class ExternalArticleRelatedItemTeaserConverter implements ViewModelConverter
{
    /**
     * @param ExternalArticle $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return Teaser::relatedItem(
            $object->getTitle(),
            $object->getUri(),
            $object->getAuthorLine(),
            new ViewModel\ContextLabel(new ViewModel\Link(ModelRelationship::get($context['from'], $object->getType(), $context['related'] ?? false))),
            null,
            TeaserFooter::forNonArticle(Meta::withText($object->getJournal()))
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ExternalArticle && !empty($context['from']) && ViewModel\Teaser::class === $viewModel && 'relatedItem' === ($context['variant'] ?? null);
    }
}
