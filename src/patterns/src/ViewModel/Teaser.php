<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Teaser implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    const STYLE_SECONDARY = 'teaser--secondary';
    const STYLE_RELATED = 'teaser--related';
    const STYLE_EVENT = 'teaser--event';
    const STYLE_CHAPTER_LISTING_ITEM = 'teaser--chapter-listing-item';
    const STYLE_GRID = 'teaser--grid-style';

    private $title;
    private $rootClasses;
    private $url;
    private $ariaLabel;
    private $content;
    private $secondaryInfo;
    private $contextLabel;
    private $eventDate;
    private $category;
    private $image;
    private $footer;

    protected function __construct(
        string $title,
        array $rootClasses = [],
        string $url = null,
        string $content = null,
        string $secondaryInfo = null,
        ContextLabel $contextLabel = null,
        Date $eventDate = null,
        Link $category = null,
        TeaserImage $image = null,
        TeaserFooter $footer = null,
        string $ariaLabel = null
    ) {
        Assertion::notBlank($title);

        $this->title = $title;
        $this->rootClasses = implode(' ', $rootClasses);
        $this->url = $url ?? false;
        $this->content = $content;
        $this->secondaryInfo = $secondaryInfo;
        $this->contextLabel = $contextLabel;
        $this->eventDate = $eventDate;
        $this->category = $category;
        $this->image = $image;
        $this->footer = $footer;
        $this->ariaLabel = $ariaLabel;
    }

    public static function relatedItem(
        string $title,
        string $url = null,
        string $secondaryInfo = null,
        ContextLabel $contextLabel = null,
        TeaserImage $image = null,
        TeaserFooter $footer = null,
        string $ariaLabel = null
    ) {
        $rootClasses = [self::STYLE_SECONDARY, self::STYLE_RELATED];

        return new static(
            $title,
            $rootClasses,
            $url,
            null,
            $secondaryInfo,
            $contextLabel,
            null,
            null,
            $image,
            $footer,
            $ariaLabel
        );
    }

    public static function main(
        string $title,
        string $url = null,
        string $content = null,
        string $secondaryInfo = null,
        ContextLabel $contextLabel = null,
        TeaserImage $image = null,
        TeaserFooter $footer = null,
        string $ariaLabel = null
    ) {
        $rootClasses = [];

        return new static(
            $title,
            $rootClasses,
            $url,
            $content,
            $secondaryInfo,
            $contextLabel,
            null,
            null,
            $image,
            $footer,
            $ariaLabel
        );
    }

    public static function withGrid(
        string $title,
        string $url = null,
        string $content = null,
        string $secondaryInfo = null,
        TeaserImage $image = null,
        TeaserFooter $footer = null,
        string $ariaLabel = null
    ) {
        $rootClasses = [self::STYLE_GRID];

        return new static(
            $title,
            $rootClasses,
            $url,
            $content,
            $secondaryInfo,
            null,
            null,
            null,
            $image,
            $footer,
            $ariaLabel
        );
    }

    public static function chapterListingItem(
        string $title,
        string $url = null,
        string $content = null,
        ContextLabel $contextLabel = null,
        TeaserFooter $footer = null
    ) {
        $rootClasses = [self::STYLE_CHAPTER_LISTING_ITEM];

        return new static(
            $title,
            $rootClasses,
            $url,
            $content,
            null,
            $contextLabel,
            null,
            null,
            null,
            $footer,
            null
        );
    }

    public static function secondary(
        string $title,
        string $url = null,
        string $secondaryInfo = null,
        ContextLabel $contextLabel = null,
        TeaserImage $image = null,
        TeaserFooter $footer = null,
        string $ariaLabel = null
    ) {
        $rootClasses = [self::STYLE_SECONDARY];

        return new static(
            $title,
            $rootClasses,
            $url,
            null,
            $secondaryInfo,
            $contextLabel,
            null,
            null,
            $image,
            $footer,
            $ariaLabel
        );
    }

    public static function event(
        string $title,
        string $url = null,
        string $secondaryInfo = null,
        Date $eventDate = null,
        bool $isSecondary = false,
        TeaserImage $image = null,
        string $ariaLabel = null
    ) {
        $rootClasses = [self::STYLE_EVENT];
        if ($isSecondary) {
            array_push($rootClasses, self::STYLE_SECONDARY);
        }

        return new static(
            $title,
            $rootClasses,
            $url,
            null,
            $secondaryInfo,
            null,
            $eventDate,
            null,
            $image,
            null,
            $ariaLabel
        );
    }

    public static function basic(
        string $title,
        string $url = null,
        TeaserImage $image = null,
        TeaserFooter $footer = null,
        string $ariaLabel = null
    ) {
        $rootClasses = [self::STYLE_SECONDARY];

        return new static(
            $title,
            $rootClasses,
            $url,
            null,
            null,
            null,
            null,
            null,
            $image,
            $footer,
            $ariaLabel
        );
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/teaser.mustache';
    }
}
