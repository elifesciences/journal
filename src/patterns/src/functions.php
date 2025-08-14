<?php

namespace eLife\Patterns;

function mixed_visibility_text(string $prefix, string $text, string $suffix = '') : string
{
    $wrappedPrefix = '';
    $wrappedSuffix = '';

    if (false === empty($prefix)) {
        $wrappedPrefix = '<span class="visuallyhidden">'.$prefix.' </span>';
    }
    if (false === empty($suffix)) {
        $wrappedSuffix = '<span class="visuallyhidden"> '.$suffix.'</span>';
    }

    return $wrappedPrefix.$text.$wrappedSuffix;
}

function mixed_accessibility_text(
    string $visibleInaccessiblePrefix,
    string $hiddenAccessibleText,
    string $visibleInaccessibleSuffix = ''
) : string {
    $string = '';

    if (false === empty($visibleInaccessiblePrefix)) {
        $string .= '<span aria-hidden="true">'.$visibleInaccessiblePrefix.'</span><span class="visuallyhidden"> ';
    } else {
        $string .= '<span class="visuallyhidden">';
    }

    $string .= $hiddenAccessibleText;

    if (false === empty($visibleInaccessibleSuffix)) {
        if (false === empty($visibleInaccessiblePrefix)) {
            $string .= '</span><span aria-hidden="true"> ';
        } else {
            $string .= ' </span><span aria-hidden="true">';
        }
        $string .= "{$visibleInaccessibleSuffix}</span>";
    } else {
        $string .= '</span>';
    }

    return $string;
}
