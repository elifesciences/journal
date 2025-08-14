<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use DateTimeImmutable;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class CallToAction implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $button;
    private $dismissible;
    private $id;
    private $image;
    private $needsJs;
    private $text;

    public function __construct(
        string $id,
        Picture $image,
        string $text,
        Button $button,
        bool $needsJs = false,
        DateTimeImmutable $cookieExpires = null
    ) {
        Assertion::notBlank($id);
        Assertion::notBlank($text);

        $this->id = $id;
        if ($needsJs) {
            $this->needsJs = $needsJs;

            if (null !== $cookieExpires) {
                $this->dismissible = [
                    'cookieExpires' => $cookieExpires->format(DATE_COOKIE),
                ];
            }
        }
        $this->image = $image;
        $this->text = $text;
        $this->button = FlexibleViewModel::fromViewModel($button)
            ->withProperty('classes', "{$button['classes']} call-to-action__button");
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/call-to-action.mustache';
    }
}
