<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class LoginControl implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $button;
    private $displayName;
    private $icon;
    private $isLoggedIn;
    private $linkFieldData;
    private $linkFieldRoots;
    private $defaultUri;
    private $subsidiaryText;

    private function __construct()
    {
    }

    public static function loggedIn(
        string $defaultUri,
        string $displayName,
        Picture $icon,
        string $subsidiaryText = null,
        array $linkFields = []
    ) : LoginControl {
        Assertion::notBlank($defaultUri);
        Assertion::notBlank($displayName);
        Assertion::notBlank($icon);

        $loggedInControl = new static();
        $loggedInControl->isLoggedIn = true;
        $loggedInControl->displayName = $displayName;
        $loggedInControl->subsidiaryText = $subsidiaryText;
        $loggedInControl->defaultUri = $defaultUri;
        $loggedInControl->icon = $icon;

        if (!empty($linkFields)) {
            Assertion::allNotBlank(array_keys($linkFields));
            Assertion::allNotBlank(array_values($linkFields));

            $loggedInControl->linkFieldRoots = $loggedInControl->buildLinkFieldRootsAttributeValue($linkFields);
            $loggedInControl->linkFieldData = $loggedInControl->buildLinkFieldsDataAttributeValues($linkFields);
        }

        return $loggedInControl;
    }

    public static function notLoggedIn(string $text, string $uri) : LoginControl
    {
        Assertion::notBlank($uri);
        Assertion::notBlank($text);

        $notLoggedInControl = new static();
        $notLoggedInControl->isLoggedIn = null;
        $notLoggedInControl->button = Button::link($text, $uri, Button::SIZE_EXTRA_SMALL, Button::STYLE_LOGIN);

        return $notLoggedInControl;
    }

    private static function buildLinkFieldRootsAttributeValue($linkFields)
    {
        return implode(', ', array_map(function (int $i) {
            return "link{$i}";
        }, range(1, count($linkFields))));
    }

    private static function buildLinkFieldsDataAttributeValues(array $linkFields)
    {
        return implode(' ', array_map(function (string $text, string $uri, int $i) {
            return "data-link{$i}-text=\"{$text}\" data-link{$i}-uri=\"{$uri}\"";
        }, array_keys($linkFields), array_values($linkFields), range(1, count($linkFields))));
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/login-control.mustache';
    }
}
