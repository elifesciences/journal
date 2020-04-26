<?php

namespace eLife\Journal\ViewModel\Converter\Block;

use DateTimeImmutable;
use eLife\ApiSdk\Model\Block;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class TweetConverter implements ViewModelConverter
{
    private $viewModelConverter;
    private $patternRenderer;

    public function __construct(ViewModelConverter $viewModelConverter, PatternRenderer $patternRenderer)
    {
        $this->viewModelConverter = $viewModelConverter;
        $this->patternRenderer = $patternRenderer;
    }

    /**
     * @param Block\Tweet $object
     */
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return new ViewModel\Tweet(
            'https://twitter.com/'.$object->getAccountId().'/status/'.$object->getId(),
            $object->getAccountId(),
            $object->getAccountLabel(),
            array_map(function (Block $block) {
                return $this->viewModelConverter->convert($block);
            }, $object->getText()),
            ViewModel\Date::simple(new DateTimeImmutable($object->getDate()->toString())),
            !$object->isConversation(),
            !$object->isMediaCard()
        );
    }

    public function supports($object, string $viewModel = null, array $context = []) : bool
    {
        return $object instanceof Block\Tweet;
    }

    protected function getViewModelConverter() : ViewModelConverter
    {
        return $this->viewModelConverter;
    }

    protected function getPatternRenderer() : PatternRenderer
    {
        return $this->patternRenderer;
    }
}
