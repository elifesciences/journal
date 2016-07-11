<?php

namespace eLife\Journal\Twig;

use eLife\Patterns\HasAssets;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;

final class AssetsExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    private $assets;

    public function __construct(HasAssets $assets)
    {
        $this->assets = $assets;
    }

    public function getGlobals()
    {
        return [
            'inline_stylesheets' => $this->assets->getInlineStyleSheets(),
            'inline_javascripts' => $this->assets->getInlineJavaScripts(),
        ];
    }

    public function getName()
    {
        return 'assets';
    }
}
