<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class MetaNew implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $url;
    private $text;
    private $date;

    private function __construct(string $url = null, string $text = null, Date $date = null)
    {
        if ($date instanceof Date) {
            Assertion::false($date['isExpanded']);
        }

        $this->url = $url ?? false;
        $this->text = $text;
        $this->date = $date;
    }

    public static function withLink(Link $link, Date $date = null) : MetaNew
    {
        return new self($link['url'], $link['name'], $date);
    }

    public static function withText(string $text, Date $date = null) : MetaNew
    {
        Assertion::minLength($text, 1);

        return new self(null, $text, $date);
    }

    public static function withDate(Date $date) : MetaNew
    {
        return new self(null, null, $date);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/meta-journal.mustache';
    }
}
