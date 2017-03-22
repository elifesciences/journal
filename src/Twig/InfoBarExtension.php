<?php

namespace eLife\Journal\Twig;

use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\InfoBar;
use Twig_Extension;
use Twig_Function;

final class InfoBarExtension extends Twig_Extension
{
    private $renderer;

    public function __construct(PatternRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getFunctions()
    {
        return [
            new Twig_Function(
                'info_bar',
                [$this, 'infoBar'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function infoBar(string $message, string $type = InfoBar::TYPE_INFO) : string
    {
        return $this->renderer->render(new InfoBar($message, $type));
    }
}
