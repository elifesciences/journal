<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class Reference implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $bibId;
    private $abstracts;
    private $authorLists;
    private $origin;
    private $label;
    private $doi;
    private $title;
    private $titleLink;
    private $hasAuthors;
    private $hasAbstracts;

    private function __construct(
        string $title,
        array $origin,
        string $label = null,
        string $id = null,
        string $titleLink = null,
        Doi $doi = null,
        array $authorLists = [],
        array $abstracts = []
    ) {
        Assertion::notBlank($title);
        Assertion::allString($origin);
        Assertion::allIsInstanceOf($authorLists, ReferenceAuthorList::class);
        Assertion::allIsInstanceOf($abstracts, Link::class);

        $this->bibId = $id;
        $this->titleLink = $titleLink;
        $this->title = $title;
        $this->doi = $doi;
        $this->origin = empty($origin) ? null : implode('. ', $origin).'.';
        $this->label = $label;
        $this->authorLists = $authorLists;
        $this->hasAuthors = !empty($authorLists);
        $this->abstracts = $abstracts;
        $this->hasAbstracts = !empty($abstracts);
    }

    public static function withDoi(
        string $title,
        Doi $doi,
        string $id = null,
        string $label = null,
        array $origin = [],
        array $authorLists = [],
        array $abstracts = []
    ) : Reference {
        return new self($title, $origin, $label, $id, null, $doi, $authorLists, $abstracts);
    }

    public static function withOutDoi(
        Link $title,
        string $id = null,
        string $label = null,
        array $origin = [],
        array $authorLists = [],
        array $abstracts = []
    ) : Reference {
        return new self($title['name'], $origin, $label, $id, $title['url'], null, $authorLists, $abstracts);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/reference.mustache';
    }
}
