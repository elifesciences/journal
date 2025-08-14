<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class TeaserImage implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $fallback;
    private $sources;
    private $type;

    const STYLE_PROMINENT = 'prominent';
    const STYLE_BIG = 'big';
    const STYLE_SMALL = 'small';

    private function __construct(
        Picture $image,
        string $type
    ) {
        $this->fallback = $image['fallback'];
        $this->sources = $image['sources'];
        $this->type = $type;
    }

    public static function prominent(Picture $image)
    {
        return new static($image, self::STYLE_PROMINENT);
    }

    public static function big(Picture $image)
    {
        return new static($image, self::STYLE_BIG);
    }

    public static function small(Picture $image)
    {
        return new static($image, self::STYLE_SMALL);
    }
}
