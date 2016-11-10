<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ComposedAssets;
use eLife\Patterns\ReadOnlyArrayAccess;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;
use Traversable;

final class AssetViewerInlineSet implements ViewModel
{
    use ArrayFromProperties;
    use ComposedAssets;
    use ReadOnlyArrayAccess;

    private $viewers;

    public function __construct(AssetViewerInline ...$viewers)
    {
        $this->viewers = $viewers;
    }

    public function getTemplateName() : string
    {
        return '/elife/journal/patterns/asset-viewer-inline-set.mustache';
    }

    protected function getComposedViewModels() : Traversable
    {
        yield from $this->viewers;
    }
}
