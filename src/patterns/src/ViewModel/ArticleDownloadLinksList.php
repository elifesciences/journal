<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class ArticleDownloadLinksList implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $id;
    private $description;
    private $groups;

    public function __construct(string $id, string $description, array $groups)
    {
        Assertion::notBlank($id);
        Assertion::notBlank($description);
        Assertion::notEmpty($groups);
        Assertion::allIsInstanceOf($groups, ArticleDownloadLinksGroup::class);

        $this->id = $id;
        $this->description = $description;
        $this->groups = $groups;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/article-download-links-list.mustache';
    }
}
