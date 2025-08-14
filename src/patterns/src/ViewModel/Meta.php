<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Meta implements ViewModel
{
    const STATUS_NOT_REVISED = 'Not revised';
    const STATUS_REVISED = 'Revised';
    const STATUS_VOR = 'Version of Record';
    const STATUS_POA = 'Accepted Manuscript';

    const COLOR_NOT_REVISED = 'not-revised';
    const COLOR_REVISED = 'revised';
    const COLOR_VOR = 'vor';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $url;
    private $text;
    private $date;
    private $articleStatus;
    private $articleStatusColorClass;
    private $version;

    private function __construct(string $url = null, string $text = null, Date $date = null, string $articleStatus = null,
    string $articleStatusColorClass = null, string $version = null)
    {
        if ($date instanceof Date) {
            Assertion::false($date['isExpanded']);
        }

        Assertion::nullOrChoice($articleStatus, [self::STATUS_NOT_REVISED, self::STATUS_REVISED, self::STATUS_VOR, self::STATUS_POA]);
        Assertion::nullOrChoice($articleStatusColorClass, [self::COLOR_NOT_REVISED, self::COLOR_REVISED, self::COLOR_VOR]);

        $this->url = $url ?? false;
        $this->text = $text;
        $this->date = $date;
        $this->articleStatus = $articleStatus;
        $this->articleStatusColorClass = $articleStatusColorClass;
        $this->version = $version;
    }

    public static function withLink(Link $link, Date $date = null, string $articleStatus = null,
    string $articleStatusColorClass = null, string $version = null) : Meta
    {
        return new self($link['url'], $link['name'], $date, $articleStatus, $articleStatusColorClass, $version);
    }

    public static function withVersion(string $version, Date $date = null, string $articleStatus = null,
    string $articleStatusColorClass = null) : Meta
    {
        return new self(null, null, $date, $articleStatus, $articleStatusColorClass, $version);
    }

    public static function withText(string $text, Date $date = null) : Meta
    {
        Assertion::minLength($text, 1);

        return new self(null, $text, $date);
    }

    public static function withDate(Date $date) : Meta
    {
        return new self(null, null, $date);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/meta.mustache';
    }
}
