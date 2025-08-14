<?php

namespace eLife\Patterns\ViewModel;

use Assert\Assertion;
use eLife\Patterns\ArrayAccessFromProperties;
use eLife\Patterns\ArrayFromProperties;
use eLife\Patterns\ViewModel;

final class SiteHeaderNavBar implements ViewModel
{
    use ArrayAccessFromProperties;
    use ArrayFromProperties;

    private $classesInner;
    private $classesOuter;
    private $linkedItems = [];

    private function __construct(array $linkedItems, string $type)
    {
        Assertion::allIsInstanceOf($linkedItems, NavLinkedItem::class);
        Assertion::notEmpty($linkedItems);

        $linkedItems = array_values($linkedItems);

        for ($i = 0; $i < count($linkedItems); ++$i) {
            $classes = ['nav-'.$type.'__item'];

            if (0 === $i) {
                $classes[] = $classes[0].'--first';
            }
            if ((count($linkedItems) - 1) === $i) {
                $classes[] = $classes[0].'--last';
            }

            if (false !== strpos(strtolower($linkedItems[$i]['text']), 'alert')) {
                $classes[] = $classes[0].'--alert';
            }

            if ('search' === $linkedItems[$i]['rel']) {
                $classes[] = $classes[0].'--search';
            }

            if (isset($linkedItems[$i]['button'])) {
                $classes[] = 'nav-secondary__item--hide-narrow';
            }

            $newLinkedItem = FlexibleViewModel::fromViewModel($linkedItems[$i])
                ->withProperty('classes', implode(' ', $classes));

            $this->linkedItems[] = $newLinkedItem;
        }

        $this->classesOuter = 'nav-'.$type;
        $this->classesInner = 'nav-'.$type.'__list clearfix';
    }

    public static function primary(array $linkedItems) : SiteHeaderNavBar
    {
        return new static($linkedItems, 'primary');
    }

    public static function secondary(array $linkedItems) : SiteHeaderNavBar
    {
        return new static($linkedItems, 'secondary');
    }

    public function getTemplateName() : string
    {
        return 'resources/templates/site-header-nav-bar.mustache';
    }
}
