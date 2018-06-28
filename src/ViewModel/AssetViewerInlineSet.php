<?php

namespace eLife\Journal\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\AssetViewerInline;

final class AssetViewerInlineSet implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $viewers;

    public function __construct(AssetViewerInline ...$viewers)
    {
        $this->viewers = $viewers;
    }

    public function getTemplateName() : string
    {
        return 'asset-viewer-inline-set.mustache';
    }
}
