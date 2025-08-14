<?php

namespace eLife\Patterns\ViewModel;

use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use function eLife\Patterns\mixed_accessibility_text;
use eLife\Patterns\ViewModel;

final class SpeechBubble implements ViewModel
{
    const ELABORATELY_EMPTY = '+';
    const LITERALLY_EMPTY = '';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $text;
    private $isSmall;
    private $isWrapped;
    private $prefix;
    private $hasPlaceholder;
    private $behaviour;

    private function __construct(string $emptinessSignifier, bool $isSmall = false, bool $isWrapped = false, string $prefix = '')
    {
        $visibleAnnotationCount = "<span data-visible-annotation-count>{$emptinessSignifier}</span>";
        $hiddenAccessibleText = 'Open annotations. The current annotation count on this page is <span data-hypothesis-annotation-count>being calculated</span>.';
        $this->text = mixed_accessibility_text($visibleAnnotationCount, $hiddenAccessibleText);
        if ($isSmall) {
            $this->isSmall = $isSmall;
        }
        if ($isWrapped) {
            $this->isWrapped = $isWrapped;
        }
        if (!empty($prefix)) {
            $this->prefix = $prefix;
        }
        if (self::ELABORATELY_EMPTY === $emptinessSignifier) {
            $this->hasPlaceholder = true;
        }
        $this->behaviour = 'HypothesisOpener';
    }

    public static function forArticleBody() : SpeechBubble
    {
        return new static(self::ELABORATELY_EMPTY, false, true, 'Add a comment');
    }

    public static function forContextualData() : SpeechBubble
    {
        return new static(self::LITERALLY_EMPTY, true);
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/speech-bubble.mustache';
    }
}
