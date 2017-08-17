<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ExternalArticle;
use eLife\Patterns\ViewModel;

final class ExternalArticleReadMoreItemConverter implements ViewModelConverter
{
    /**
     * @param ExternalArticle $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ReadMoreItem(
            new ViewModel\ContentHeaderReadMore(
                $object->getTitle(),
                $object->getUri(),
                [],
                $object->getAuthorLine(),
                ViewModel\Meta::withText($object->getJournal())
            ),
            $context['isRelated'] ?? false
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ExternalArticle && ViewModel\ReadMoreItem::class === $viewModel;
    }
}
