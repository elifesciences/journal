<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class ArticleDownloadLinksGroup implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $jsHideGroup;
    private $id;
    private $title;
    private $items;
    private $intro;

    public function __construct(
        string $title,
        array $items,
        string $intro = null,
        string $id = null,
        bool $jsHideGroup = false
    )
    {
        Assertion::notBlank($title);
        Assertion::notEmpty($items);
        Assertion::allIsInstanceOf($items, ArticleDownloadLink::class);

        $this->jsHideGroup = $jsHideGroup;
        $this->id = $id;
        $this->title = $title;
        $this->items = $items;
        $this->intro = $intro;
    }
}
