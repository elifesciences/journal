<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

class ContentHeaderImage implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $fallback;
    private $sources;
    private $pictureClasses;
    private $credit;
    private $creditOverlay;

    public function __construct(Picture $picture, string $credit = null, bool $creditOverlay = false)
    {
        $this->fallback = $picture['fallback'];
        $this->sources = $picture['sources'];
        $this->pictureClasses = $picture['pictureClasses'];
        if ($credit) {
            $this->credit = [
                'text' => $credit,
                'elementId' => hash('crc32', json_encode($picture->toArray()).$credit),
            ];
        }
        $this->creditOverlay = $creditOverlay;
    }
}
