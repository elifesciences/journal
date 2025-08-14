<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class AssetViewerInline implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $id;
    private $variant;
    private $isSupplement;
    private $supplementOrdinal;
    private $parentId;
    private $label;
    private $supplementCount;
    private $hasMultipleSupplements;
    private $seeAllLink;
    private $captionedAsset;
    private $additionalAssets;
    private $download;
    private $open;

    private function __construct(
        string $id,
        int $supplementOrdinal = null,
        string $parentId = null,
        string $label,
        CaptionedAsset $captionedAsset,
        array $additionalAssets = [],
        Link $download = null,
        OpenLink $open = null,
        int $supplementCount = 0,
        string $seeAllLink = null
    ) {
        Assertion::notBlank($id);
        Assertion::nullOrMin($supplementOrdinal, 1);
        Assertion::nullOrNotBlank($parentId);
        Assertion::notBlank($label);
        Assertion::allIsInstanceOf($additionalAssets, AdditionalAsset::class);
        Assertion::nullOrFalse($captionedAsset['inline'], 'The captioned asset cannot be inline');

        $this->id = $id;
        if ($supplementOrdinal) {
            $this->isSupplement = true;
            $this->variant = 'supplement';
        } elseif ($captionedAsset['image']) {
            $this->variant = 'figure';
        } elseif ($captionedAsset['video']) {
            $this->variant = 'video';
        } elseif ($captionedAsset['table']) {
            $this->variant = 'table';
        }
        $this->supplementOrdinal = $supplementOrdinal;
        $this->parentId = $parentId;
        $this->label = $label;
        if ($supplementCount >= 1) {
            $this->supplementCount = $supplementCount;
            $this->hasMultipleSupplements = $supplementCount > 1;
            $this->seeAllLink = $seeAllLink;
        }
        $this->captionedAsset = $captionedAsset;
        if (!empty($additionalAssets)) {
            $this->additionalAssets = [new AdditionalAssets(null, $additionalAssets)];
        } else {
            $this->additionalAssets = [];
        }
        if ($download) {
            $this->download = [
                'link' => $download['url'],
                'filename' => $download['name'],
            ];
        }
        $this->open = $open;
    }

    public static function primary(
        string $id,
        string $label,
        CaptionedAsset $captionedAsset,
        array $additionalAssets = [],
        Link $download = null,
        OpenLink $open = null,
        int $supplementCount = 0,
        string $seeAllLink = null
    ) : AssetViewerInline {
        return new self($id, null, null, $label, $captionedAsset, $additionalAssets, $download, $open, $supplementCount, $seeAllLink);
    }

    public static function supplement(
        string $id,
        int $ordinal,
        string $parentId,
        string $label,
        CaptionedAsset $captionedAsset,
        array $additionalAssets = [],
        Link $download = null,
        OpenLink $open = null
    ) : AssetViewerInline {
        return new self($id, $ordinal, $parentId, $label, $captionedAsset, $additionalAssets, $download, $open);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/asset-viewer-inline.mustache';
    }
}
