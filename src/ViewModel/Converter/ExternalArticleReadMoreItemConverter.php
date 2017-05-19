<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ExternalArticle;
use eLife\ApiSdk\Model\Subject;
use eLife\Journal\Helper\CanConvertContent;
use eLife\Journal\Helper\ModelName;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ExternalArticleReadMoreItemConverter implements ViewModelConverter
{
    /**
     * @param ArticleVersion $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\ReadMoreItem(
            new ViewModel\ContentHeaderReadMore(
                $object->getTitle(),
                $object->getUri(),
                [],
                $object->getAuthorLine()
                /*
                 * we don't have a published date
                 * we have a article-type, but not a clickable one as there is
                 * no index of external-article
                ViewModel\Meta::withLink(
                    new ViewModel\Link(
                        ModelName::singular($object->getType()),
                        $this->urlGenerator->generate('article-type', ['type' => $object->getType()])
                    ),
                    $this->simpleDate($object, $context)
                )
                */
            )
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof ExternalArticle && ViewModel\ReadMoreItem::class === $viewModel;
    }
}
