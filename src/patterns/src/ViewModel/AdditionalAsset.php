<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class AdditionalAsset implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $assetId;
    private $captionText;
    private $nonDoiLink;
    private $doi;
    private $downloadLink;

    private function __construct(
        string $id,
        CaptionText $captionText = null,
        DownloadLink $downloadLink = null,
        string $nonDoiLink = null,
        Doi $doi = null
    ) {
        Assertion::notBlank($id);

        if ($doi) {
            $doi = FlexibleViewModel::fromViewModel($doi)
                ->withProperty('variant', Doi::ASSET);
        }

        $this->assetId = $id;
        $this->captionText = $captionText;
        $this->nonDoiLink = $nonDoiLink;
        $this->doi = $doi;
        $this->downloadLink = $downloadLink;
    }

    public static function withDoi(
        string $id,
        CaptionText $captionText,
        DownloadLink $downloadLink = null,
        Doi $doi
    ) {
        return new static($id, $captionText, $downloadLink, null, $doi);
    }

    public static function withoutDoi(
        string $id,
        CaptionText $captionText,
        DownloadLink $downloadLink = null,
        string $uri
    ) {
        return new static($id, $captionText, $downloadLink, $uri);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/additional-asset.mustache';
    }
}
