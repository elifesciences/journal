<?php

namespace eLife\Patterns\ViewModel;

trait HasTitleLength
{
    private static $titleLengthLimits = [
        19 => 'xx-short',
        35 => 'x-short',
        46 => 'short',
        57 => 'medium',
        80 => 'long',
        120 => 'x-long',
        null => 'xx-long',
    ];

    private function determineTitleLength(string $title, array $overrideLimits = null) : string
    {
        $limits = $overrideLimits ?? self::$titleLengthLimits;

        $charCount = mb_strlen(strip_tags($title));

        foreach ($limits as $maxLength => $value) {
            if ($charCount <= $maxLength) {
                return $value;
            }
        }

        return end($limits);
    }
}
