<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Tweet implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $url;
    private $accountId;
    private $accountLabel;
    private $text;
    private $date;
    private $hideConversation;
    private $hideCards;

    public function __construct(string $url, string $accountId, string $accountLabel, string $text, Date $date, bool $hideConversation = true, bool $hideCards = true)
    {
        if ($date instanceof Date) {
            Assertion::false($date['isExpanded']);
        }

        $this->url = $url;
        $this->accountId = $accountId;
        $this->accountLabel = $accountLabel;
        $this->text = $text;
        $this->date = $date;
        $this->hideConversation = $hideConversation;
        $this->hideCards = $hideCards;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/tweet.mustache';
    }
}
