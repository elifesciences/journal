<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class AnnotationTeaser implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    const RESTRICTED_ACCESS_TEXT = 'Only me';

    private $content;
    private $document;
    private $highlight;
    private $isReply;
    private $meta;
    private $inContextUri;

    private function __construct(
        string $document,
        Date $date,
        string $inContextUri,
        string $highlight = null,
        string $content = null,
        bool $isRestrictedAccess = false,
        bool $isReply = false
    ) {
        Assertion::notBlank($document);
        Assertion::notBlank($inContextUri);
        Assertion::nullOrNotBlank($highlight);
        Assertion::nullOrNotBlank($content);

        $this->document = $document;
        $this->inContextUri = $inContextUri;
        $this->highlight = $highlight;
        $this->content = $content;

        if ($isRestrictedAccess) {
            $this->meta = Meta::withText(self::RESTRICTED_ACCESS_TEXT, $date);
        } else {
            $this->meta = Meta::withDate($date);
        }

        if ($isReply) {
            $this->isReply = $isReply;
        }
    }

    public static function forAnnotation(
        string $document,
        Date $date,
        string $inContextUri,
        string $highlight,
        string $content,
        bool $isRestricted = false
    ) : AnnotationTeaser {
        return new static(
            $document,
            $date,
            $inContextUri,
            $highlight,
            $content,
            $isRestricted
        );
    }

    public static function forHighlight(
        string $document,
        Date $date,
        string $inContextUri,
        string $highlight,
        bool $isRestricted = false
    ) : AnnotationTeaser {
        return new static(
            $document,
            $date,
            $inContextUri,
            $highlight,
            null,
            $isRestricted
        );
    }

    public static function forPageNote(
        string $document,
        Date $date,
        string $inContextUri,
        string $content,
        bool $isRestricted = false
    ) : AnnotationTeaser {
        return new static(
            $document,
            $date,
            $inContextUri,
            null,
            $content,
            $isRestricted
        );
    }

    public static function forReply(
        string $document,
        Date $date,
        string $inContextUri,
        string $content,
        bool $isRestricted = false
    ) : AnnotationTeaser {
        return new static(
            $document,
            $date,
            $inContextUri,
            null,
            $content,
            $isRestricted,
            true
        );
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/annotation-teaser.mustache';
    }
}
