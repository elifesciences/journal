<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\CastsToArray;

final class TeaserFooter implements CastsToArray
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $meta;
    private $terms;

    private function __construct(
        Meta $meta,
        TeaserTerms $terms = null
    ) {
        $this->meta = $meta;
        $this->terms = $terms;
    }

    public static function forArticle(
        Meta $meta,
        TeaserTerms $terms = null
    ) {
        return new self($meta, $terms);
    }

    public static function forNonArticle(
        Meta $meta
    ) {
        return new self($meta);
    }
}
