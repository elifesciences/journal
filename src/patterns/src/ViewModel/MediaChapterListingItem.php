<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class MediaChapterListingItem implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $title;
    private $startTime;
    private $chapterNumber;
    private $content;
    private $hasContentSources;
    private $contentSources;

    public function __construct(
        string $title,
        int $startTime,
        int $chapterNumber,
        string $content = null,
        array $contentSources = []
    ) {
        Assertion::minLength($title, 1);
        Assertion::min($startTime, 0);
        Assertion::min($chapterNumber, 1);
        Assertion::allIsInstanceOf($contentSources, ContentSource::class);

        $this->title = $title;

        if (null !== $startTime) {
            $minutes = floor($startTime / 60);
            $seconds = str_pad($startTime % 60, 2, '0', STR_PAD_LEFT);
            $this->startTime = [
                'forMachine' => $startTime,
                'forHuman' => sprintf('%s:%s', $minutes, $seconds),
            ];
        }

        $this->chapterNumber = $chapterNumber;
        $this->content = $content;
        if ($contentSources) {
            $this->hasContentSources = true;
            $this->contentSources = $contentSources;
        }
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/media-chapter-listing-item.mustache';
    }
}
