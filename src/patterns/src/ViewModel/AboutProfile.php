<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class AboutProfile implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $name;
    private $role;
    private $image;
    private $hasPlaceholderImage;
    private $profile;

    public function __construct(string $name, string $role = null, Picture $image = null, bool $hasPlaceholderImage = false, string $profile = null)
    {
        Assertion::notBlank($name);

        $this->name = $name;
        $this->role = $role;
        $this->image = $image;
        if ($hasPlaceholderImage) {
            $this->hasPlaceholderImage = $hasPlaceholderImage;
        }
        $this->profile = $profile;
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/about-profile.mustache';
    }
}
