<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ContentHeaderNew implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;
    use HasTitleLength;

    private $title;
    private $hasAside;
    private $isOneColumn;
    private $titleLength;
    private $image;
    private $impactStatement;
    private $header;
    private $breadcrumb;
    private $authors;
    private $download;
    private $cite;
    private $socialMediaSharers;
    private $contextualData;
    private $selectNav;
    private $meta;
    private $doi;
    private $licence;
    private $audioPlayer;

    public function __construct(
        string $title,
        bool $hasAside = false,
        bool $isOneColumn = null,
        ContentHeaderImage $image = null,
        string $impactStatement = null,
        bool $header = null,
        Breadcrumb $breadcrumb = null,
        array $subjects = [],
        Profile $profile = null,
        Authors $authors = null,
        string $download = null,
        string $cite = null,
        SocialMediaSharersNew $socialMediaSharers = null,
        ContextualData $contextualData = null,
        SelectNav $selectNav = null,
        MetaNew $meta = null,
        Doi $doi = null,
        string $licence = null,
        AudioPlayer $audioPlayer = null
    ) {
        Assertion::notBlank($title);
        Assertion::allIsInstanceOf($subjects, Link::class);

        if (true === $hasAside) {
            $this->hasAside = $hasAside;
        }

        if (true === $isOneColumn) {
            $this->isOneColumn = $isOneColumn;
        }
        $this->title = $title;
        $this->titleLength = $this->determineTitleLength($this->title);

        $this->image = $image;
        $this->impactStatement = $impactStatement;
        if ($header) {
            $this->header = ['possible' => true];
            if ($subjects) {
                $this->header['hasSubjects'] = true;
                $this->header['subjects'] = $subjects;
            }
            if ($profile) {
                $this->header['hasProfile'] = true;
                $this->header['profile'] = $profile;
            }
        }
        $this->authors = $authors;
        $this->breadcrumb = $breadcrumb;
        $this->download = $download;
        $this->cite = $cite;
        $this->socialMediaSharers = $socialMediaSharers;
        $this->contextualData = $contextualData;
        $this->selectNav = $selectNav;
        $this->meta = $meta;
        $this->doi = $doi;
        $this->licence = $licence;
        $this->audioPlayer = $audioPlayer;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/content-header-journal.mustache';
    }
}
