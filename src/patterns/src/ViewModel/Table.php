<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

/**
 * @SuppressWarnings(ForbiddenAbleSuffix)
 */
final class Table implements ViewModel, IsCaptioned
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $tables;
    private $hasFootnotes;
    private $footnotes;

    public function __construct(array $tables, array $footnotes = [])
    {
        Assertion::allRegex($tables, '/^<table>[\s\S]+<\/table>$/');
        Assertion::notEmpty($tables);
        Assertion::allIsInstanceOf($footnotes, TableFootnote::class);

        $this->tables = $tables;
        if (!empty($footnotes)) {
            $this->hasFootnotes = true;
            $this->footnotes = $footnotes;
        }
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/table.mustache';
    }
}
