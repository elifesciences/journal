<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use DateTimeImmutable;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class InfoBar implements ViewModel
{
    const TYPE_ATTENTION = 'attention';
    const TYPE_DISMISSIBLE = 'dismissible';
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_CORRECTION = 'correction';
    const TYPE_MULTIPLE_VERSIONS = 'multiple-versions';
    const TYPE_WARNING = 'warning';
    const TYPE_ANNOUNCEMENT = 'announcement';

    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $dismissible;
    private $id;
    private $text;
    private $type;

    public function __construct(
        string $text,
        string $type = self::TYPE_INFO,
        string $id = null,
        DateTimeImmutable $cookieExpires = null)
    {
        Assertion::notBlank($text);
        Assertion::choice($type, [
            self::TYPE_ATTENTION,
            self::TYPE_DISMISSIBLE,
            self::TYPE_INFO,
            self::TYPE_SUCCESS,
            self::TYPE_CORRECTION,
            self::TYPE_MULTIPLE_VERSIONS,
            self::TYPE_WARNING,
            self::TYPE_ANNOUNCEMENT,
        ]);

        if (self::TYPE_DISMISSIBLE === $type) {
            Assertion::notBlank($id);
            if (null !== $cookieExpires) {
                $this->dismissible = [
                    'cookieExpires' => $cookieExpires->format(DATE_COOKIE),
                ];
            }
        }

        $this->id = $id;
        $this->text = $text;
        $this->type = $type;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/info-bar.mustache';
    }
}
