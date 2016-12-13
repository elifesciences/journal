<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\SimplifyAssets;
use eLife\Patterns\ViewModel;

/**
 * @SuppressWarnings(ForbiddenAbleSuffix)
 */
final class Table implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;
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
