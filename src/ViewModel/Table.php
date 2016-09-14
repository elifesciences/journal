<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ReadOnlyArrayAccess;
use eLife\Patterns\SimplifyAssets;
use eLife\Patterns\ViewModel;

final class Table implements ViewModel
{
    use ArrayFromProperties;
    use ReadOnlyArrayAccess;
    use SimplifyAssets;

    private $table;
    private $footer;

    public function __construct(string $table, string $footer = null)
    {
        $this->table = $table;
        $this->footer = $footer;
    }

    public function getTemplateName() : string
    {
        return '/elife/journal/patterns/table.mustache';
    }
}
